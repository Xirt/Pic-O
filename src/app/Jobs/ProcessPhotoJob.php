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

/**
 * Handles the processing of a single photo file.
 *
 * This job reads the photo from disk, extracts metadata (EXIF, dimensions, etc.),
 * generates thumbnails or blurhashes, and stores or updates the corresponding
 * Photo model in the database.
 */
class ProcessPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string Absolute filesystem path to the photo */
    protected string $path;

    /** @var string ID of the Folder the photo belongs to */
    protected int $folderId;

    /** @var string Filename of the photo */
    protected string $filename;

    /** @var string Relative path of the photo */
    protected string $relativePath;

    /** @var string Scanner log channel (for information logging) */
    private const LOG_CHANNEL = 'scanner';

    /**
     * Filename date/time extraction patterns for various well-known formats.
     *
     * @var array<int, string>
     */
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
     * @param int    $folderId Folder ID the photo belongs to
     * @param string $path     Absolute filesystem path
     */
    public function __construct(int $folderId, string $path)
    {
        $this->folderId     = $folderId;
        $this->path         = $path;

        $this->filename     = basename($path);
        $this->relativePath = Str::after($path, resource_path() . DIRECTORY_SEPARATOR);
    }

    /**
     * Process the photo.
     *
     * @param PhotoService $photoService
     *
     * @return void
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
        [$width, $height] = array_values($this->getImageDims($this->path));
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

    /**
     * Get last database update for this photo record.
     *
     * @return Carbon|null
     */
    private function getLastRecordUpdate(): ?Carbon
    {
        $photo = Photo::where([
            'folder_id' => $this->folderId,
            'filename'  => $this->filename,
        ])->first();

        return $photo?->updated_at;
    }

    /**
     * Get the width and height of a given image
     *
     * @param string $path Absolute path to the image
     *
     * @return array{width:int|null,height:int|null}
     */
    private function getImageDims(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['heic', 'heif']) && class_exists('Imagick'))
        {
            try
            {
                $img = new \Imagick($path);
                return [
                    'width'  => $img->getImageWidth(),
                    'height' => $img->getImageHeight(),
                ];
            } catch (\Throwable) {}
        }
        else if ($dims = @getimagesize($path))
        {
            return [
                'width'  => $dims[0],
                'height' => $dims[1],
            ];
        }

        return [
            'width' => null,
            'height' => null
        ];
    }

    /**
     * Retrieve EXIF metadata from the photo.
     *
     * @return array<string, mixed>
     */
    private function getEXIFData(): array
    {
        $extension = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
        if (in_array($extension, ['heic', 'heif']) && class_exists('Imagick'))
        {
            return $this->normalizeMetadata($this->getHEICMetadata());
        }

        return $this->normalizeMetadata($this->getMetadata());
    }

    /**
     * Extract metadata from HEIC/HEIF images via Imagick
     *
     * @return array<string, mixed>
     */
    private function getHEICMetadata(): array
    {
        try
        {
            $img = new \Imagick($this->path);
            $exifRaw = $img->getImageProperties('exif:*');

            return [
                'camera'        => $exifRaw['exif:Model'] ?? null,
                'make'          => $exifRaw['exif:Make'] ?? null,
                'orientation'   => isset($exifRaw['exif:Orientation']) ? (int)$exifRaw['exif:Orientation'] : null,
                'aperture'      => $exifRaw['exif:FNumber'] ?? null,
                'iso'           => isset($exifRaw['exif:ISOSpeedRatings']) ? (int)$exifRaw['exif:ISOSpeedRatings'] : null,
                'focal_length'  => $this->interpretValue($exifRaw['exif:FocalLength'] ?? null),
                'exposure_time' => $this->interpretValue($exifRaw['exif:ExposureTime'] ?? null),
                'taken_at'      => isset($exifRaw['exif:DateTimeOriginal']) ? Carbon::parse($exifRaw['exif:DateTimeOriginal'], 'UTC') : null,
                'shutter_speed' => null,
                'exif_raw'      => $exifRaw,
            ];
        } catch (\Throwable) {}

        return [];
    }

    /**
     * Extract metadata from JPEG/TIFF images via PHP EXIF
     *
     * @return array<string, mixed>
     */
    private function getMetadata(): array
    {
        if ($exif = @exif_read_data($this->path))
        {

            return [
                'camera'        => $exif['Model'] ?? null,
                'make'          => $exif['Make'] ?? null,
                'orientation'   => $exif['Orientation'] ?? null,
                'aperture'      => $exif['COMPUTED']['ApertureFNumber'] ?? null,
                'iso'           => $exif['ISOSpeedRatings'] ?? null,
                'focal_length'  => $this->interpretValue($exif['FocalLength'] ?? null),
                'exposure_time' => $this->interpretValue($exif['ExposureTime'] ?? null),
                'taken_at'      => $this->getTakenAt($exif),
                'shutter_speed' => null,
            ];
        }

        return [];
    }

    /**
     * Normalize metadata: trim strings, calculate shutter speed, format focal length and exposure time
     *
     * @param array<string, mixed> $metadata
     *
     * @return array<string, mixed>
     */
    private function normalizeMetadata(array $metadata): array
    {
        // Remove obsolete spaces
        $metadata = array_map(function ($value) {
            return is_string($value) ? Str::trim($value) : $value;
        }, $metadata);

        // Attempt to determine shutter speed
        $shutterSource = $metadata['exif_raw']['exif:ShutterSpeedValue'] ?? $metadata['shutter_speed'] ?? null;
        if (empty($metadata['exposure_time']) && $shutterSource !== null)
        {
            $apex = $this->interpretValue($exif['ShutterSpeedValue'] ?? null);
            if (is_numeric($apex)) {
                $metadata['shutter_speed'] = 1 / pow(2, (float) $apex);
            }
        }

        unset($metadata['exif_raw']);

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
     * Determine photo capture time from EXIF or filename.
     *
     * @param array<string, mixed> $exif
     *
     * @return Carbon|null
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
     * Interpret EXIF numeric or fractional values.
     *
     * @param string|null $value
     *
     * @return float|null
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
