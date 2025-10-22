<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Carbon\Carbon;
use App\Models\Photo;
use App\Services\PhotoService;

class ProcessPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;

    protected int $folderId;

    protected string $filename;

    protected string $relativePath;

    private const LOG_CHANNEL = 'scanner';

    /**
     * Constructor
     */
    public function __construct(int $folderId, string $path)
    {
        $this->folderId     = $folderId;
        $this->path         = $path;

        $this->filename     = basename($path);
        $this->relativePath = Str::after($path, resource_path() . DIRECTORY_SEPARATOR);
    }

    /**
     * Process a photo for later use
     */
    public function handle(PhotoService $photoService): void
    {  
        Log::channel(self::LOG_CHANNEL)->info("Processing photo: $this->relativePath");

        if (!file_exists($this->path) || !is_readable($this->path))
        {
            Log::channel(self::LOG_CHANNEL)->warning("Photo inaccessible: $this->relativePath");
            return;
        }

        // Gather photo metadata
        $dims = @getimagesize($this->path);
        $metadata = array_merge([
            'size'     => @filesize($this->path),
            'taken_at' => Carbon::createFromTimestamp(@filectime($this->path))
        ], $this->getEXIFData());

        // Store the photo
        $photo = Photo::updateOrCreate([
            'folder_id' => $this->folderId,
            'filename'  => $this->filename,
        ], array_merge([
            'height'    => $dims ? $dims[1] : null,
            'width'     => $dims ? $dims[0] : null,
        ], $metadata));

        // Attempt to create thumbnails
        $photoService->thumbnail($photo);
        $photo->blurhash = $photoService->blurhash($photo);
        $photo->save();

        Log::channel(self::LOG_CHANNEL)->info("Processed: $this->relativePath");
    }

    /**
     * Returns all retrieved EXIF metadata of the current photo
     */
    private function getEXIFData(): array
    {
        $extension = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'tiff']))
        {
            return [];
        }

        // Attempt to retrieve EXIF data
        if (!$exif = @exif_read_data($this->path))
        {
            return [];
        }

        $metadata = [
            'camera'        => $exif['Model'] ?? null,
            'make'          => $exif['Make'] ?? null,
            'orientation'   => $exif['Orientation'] ?? null,
            'aperture'      => $exif['COMPUTED']['ApertureFNumber'] ?? null,
            'iso'           => $exif['ISOSpeedRatings'] ?? null,
            'focal_length'  => $this->interpretValue($exif['FocalLength'] ?? null),
            'exposure_time' => $this->interpretValue($exif['ExposureTime'] ?? null),
            'taken_at'      => $this->getTakenAt($exif),
        ];

        // Remove obsolete spaces
        $metadata = array_map(function ($value) {
            return is_string($value) ? Str::trim($value) : $value;
        }, $metadata);

        // Attempt to determine shutter speed
        if (empty($exif['ExposureTime']) && !empty($exif['ShutterSpeedValue']))
        {
            $apex = $this->interpretValue($exif['ShutterSpeedValue'] ?? null);
            if (is_numeric($apex)) {
                $metadata['shutter_speed'] = 1 / pow(2, (float) $apex);
            }
        }

        // Formatting for focal length (in mm)
        if (!empty($metadata['focal_length']))
        {
            $metadata['focal_length'] = round($metadata['focal_length']) . ' mm';
        }

        // Formatting for exposure time (in sec)
        if (!empty($metadata['exposure_time']))
        {
            if ($metadata['exposure_time'] >= 1)
            {
                $metadata['exposure_time'] = round($metadata['exposure_time'], 1) . ' sec';
            }
            else
            {
                $denominator = round(1 / $metadata['exposure_time']);
                $metadata['exposure_time'] = "1/{$denominator} sec";
            }
        }

        return $metadata;
    }


	/**
	 * Checks various EXIF values to determine date/time taken
	 */
	private function getTakenAt(array $exif): Carbon
	{
        $candidates = ['DateTimeOriginal', 'DateTimeDigitized', 'CreateDate', 'ModifyDate'];
        foreach ($candidates as $candidate)
        {
            if (empty($exif[$candidate]) || preg_match('/^0{4}:0{2}:0{2}/', $exif[$candidate]))
            {
                continue;
            }

			try
            {
                $dateString = preg_replace('/^(\d{4}):(\d{2}):(\d{2})/', '$1-$2-$3', $exif[$candidate]);
				return Carbon::parse($dateString, 'UTC');
			} catch (\Exception $e) {}

        }

        return $this->getBestKnownDate();
	}


	/**
	 * Fallback function to determine date/time taken
	 */
	private function getBestKnownDate(): Carbon
    {
        $ctime = @filectime($this->path);
        $mtime = @filemtime($this->path);

        if ($timestamp = $ctime && $ctime > 0 ? $ctime : ($mtime && $mtime > 0 ? $mtime : null))
        {
            return Carbon::createFromTimestamp($timestamp);
        }

        return Carbon::create(1, 1, 1, 0, 0, 0, 'UTC');
    }


    /**
     * Interprets a given EXIF value based on its specific format
     */
    private function interpretValue(?string $value): ?float
    {
        if (is_null($value))
        {
            return null;
        }

        if (strpos($value, '/') !== false)
        {
            list($num, $den) = explode('/', $value);
            if ((float) $den == 0.0) return null;
            return (float) $num / (float) $den;
        }

        return is_numeric($value) ? (float)$value : null;
    }
}
