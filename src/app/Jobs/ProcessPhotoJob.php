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

    private const FILENAME_PATTERNS = [
        // Canon / Android / typical pattern: IMG_20251102_101500.jpg
        '/(?P<y>20\d{2})(?P<m>\d{2})(?P<d>\d{2})[_-]?(?P<h>\d{2})(?P<i>\d{2})(?P<s>\d{2})?/',

        // ISO-style with delimiters: 2025-11-02 10:15:00 or 2025-11-02_10-15-00
        '/(?P<y>20\d{2})[-_](?P<m>\d{2})[-_](?P<d>\d{2})[ T-_.]?(?P<h>\d{2})[:_-]?(?P<i>\d{2})[:_-]?(?P<s>\d{2})?/',

        // "YYYYMMDD-HHMM" (no seconds)
        '/(?P<y>20\d{2})(?P<m>\d{2})(?P<d>\d{2})[-_ ](?P<h>\d{2})(?P<i>\d{2})\b/',

        // "YYYY.MM.DD HH.MM.SS" (common in exported files)
        '/(?P<y>20\d{2})[.](?P<m>\d{2})[.](?P<d>\d{2})[ _T-]?(?P<h>\d{2})[.:-]?(?P<i>\d{2})[.:-]?(?P<s>\d{2})?/',

        // "DDMMYYYY_HHMMSS" (European format)
        '/(?P<d>\d{2})(?P<m>\d{2})(?P<y>20\d{2})[_-]?(?P<h>\d{2})(?P<i>\d{2})(?P<s>\d{2})?/',

        // Apple/HEIC style: "2025-11-02 10.15.00"
        '/(?P<y>20\d{2})[-_.]?(?P<m>\d{2})[-_.]?(?P<d>\d{2})[ T_.-]?(?P<h>\d{2})[:_.-]?(?P<i>\d{2})[:_.-]?(?P<s>\d{2})?/',

        // WhatsApp-style: "IMG-20251102-WA0001.jpg" ? derive date only
        '/IMG[-_](?P<y>20\d{2})(?P<m>\d{2})(?P<d>\d{2})[-_]/',

        // Google Photos export: "PXL_20251102_101500.jpg"
        '/PXL[-_](?P<y>20\d{2})(?P<m>\d{2})(?P<d>\d{2})[_-]?(?P<h>\d{2})(?P<i>\d{2})(?P<s>\d{2})?/',
    ];

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

        $lastUpdate = $this->getLastRecordUpdate();

        // Gather photo metadata
        $metadata = array_merge([
            'size' => @filesize($this->path)
        ], $this->getEXIFData());

        // (Corrected) dimensions
        $dims = @getimagesize($this->path);
        $width  = $dims ? $dims[0] : null;
        $height = $dims ? $dims[1] : null;

        if (in_array($metadata['orientation'], [5, 6, 7, 8])) {
            [$width, $height] = [$height, $width];
        }

        // Store the photo
        $photo = Photo::updateOrCreate([
            'folder_id' => $this->folderId,
            'filename'  => $this->filename,
        ], array_merge([
            'width'     => $width,
            'height'    => $height,
        ], $metadata));

        // Attempt to create thumbnails
        $fileTime = @filemtime($this->path);
        if ($lastUpdate === null || Carbon::createFromTimestamp($fileTime)->gt($lastUpdate))
        {
            $photoService->thumbnail($photo);
            $photo->blurhash = $photoService->blurhash($photo);
            $photo->save();
        }

        Log::channel(self::LOG_CHANNEL)->info("Processed: $this->relativePath");
    }

    private function getLastRecordUpdate(): ?Carbon
    {
        $photo = Photo::where([
            'folder_id' => $this->folderId,
            'filename'  => $this->filename,
        ])->first();

        return $photo?->updated_at;
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
	private function getTakenAt(array $exif): ?Carbon
	{
        $candidates = ['DateTimeOriginal', 'CreateDate', 'DateTimeCreated', 'DateTimeDigitized'];
        foreach ($candidates as $candidate)
        {
            if (empty($exif[$candidate]) || preg_match('/^0{4}:0{2}:0{2}/', $exif[$candidate]))
            {
                continue;
            }

			try
            {

                $tz = $exif['OffsetTimeOriginal'] ?? 'UTC';
                $dateString = preg_replace('/^(\d{4}):(\d{2}):(\d{2})/', '$1-$2-$3', $exif[$candidate]);

				return Carbon::parse($dateString, $tz);

			} catch (\Exception $e) {}
        }

        foreach (self::FILENAME_PATTERNS as $pattern)
        {
            try
            {
                if (preg_match($pattern, $this->filename, $m))
                {

                    $h = $m['h'] ?? '00';
                    $i = $m['i'] ?? '00';
                    $s = $m['s'] ?? '00';
                    $formatted = sprintf(
                        '%04d-%02d-%02d %02d:%02d:%02d',
                        $m['y'], $m['m'], $m['d'], $h, $i, $s
                    );

                    return Carbon::parse($formatted, 'UTC');
                }
            } catch (\Exception $e) {}
        }

        return null;
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
