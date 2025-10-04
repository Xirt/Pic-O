<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;
use App\Models\Photo;
use App\Services\PhotoService;

class ProcessPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $folderId;

    protected string $filename;

    protected string $fullPath;

    /**
     * Constructor
     */
    public function __construct(int $folderId, string $fullPath)
    {
        $this->folderId = $folderId;
        $this->fullPath = $fullPath;
        $this->filename = basename($fullPath);
    }

    /**
     * Process a photo for later use
     */
    public function handle(PhotoService $photoService): void
    {
        if (file_exists($this->fullPath) && is_readable($this->fullPath))
        {
            $photo = Photo::where('folder_id', $this->folderId)
                ->where('filename', $this->filename)
                ->first();

            $fileModifiedTime = filemtime($this->fullPath);
            if ($photo && $photo->updated_at->timestamp > $fileModifiedTime)
            {
                return;
            }

            // Gather photo metadata
            $dims = @getimagesize($this->fullPath);
            $metadata = array_merge($this->getEXIFData($fileModifiedTime), [
                'size' => filesize($this->fullPath),
            ]);

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

        }

    }

    /**
     * Returns all retrieved EXIF metadata of the current photo
     */
    private function getEXIFData(int $fileModifiedTime): array
    {
        if (!in_array(strtolower(pathinfo($this->filename, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'tiff']))
        {
            return [];
        }

        // Attempt to retrieve EXIF data
        $exif = @exif_read_data($this->fullPath, 'IFD0');
        if ($exif === false)
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
            'taken_at'      => Carbon::createFromTimestamp($fileModifiedTime),
        ];

        // Attempt to determine shutter speed
        if (empty($exif['ExposureTime']) && !empty($exif['ShutterSpeedValue']))
        {
            $apex = $this->interpretValue($exif['ShutterSpeedValue'] ?? null);
            if (is_numeric($apex)) {
                $metadata['shutter_speed'] = 1 / pow(2, (float) $apex);
            }
        }

        // Fallback for 'taken at'
        if (!empty($exif['DateTime']))
        {
            $dateString = str_replace(':', '-', substr($exif['DateTime'], 0, 10)) . substr($exif['DateTime'], 10);

            try
            {
                $metadata['taken_at'] = Carbon::parse($dateString);
            } catch (\Exception $e) {}
        }

        // Formatting for focal length (in mm)
        if (!empty($metadata['focal_length']))
        {
            $metadata['focal_length'] = $metadata['focal_length'] . ' mm';
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
     * Interprets a given EXIF value based on its specific format
     */
    private function interpretValue(?string $value): mixed
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
