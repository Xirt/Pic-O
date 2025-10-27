<?php

namespace App\Services;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Illuminate\Http\Response as BinaryStreamResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use App\Models\Photo;
use App\ValueObjects\Position;
use App\ValueObjects\Dimension;
use kornrunner\Blurhash\Blurhash;

class PhotoService
{
    private const RENDER_DIMS = 1920;

    // Collection configuration
    private const CANVAS_WIDTH  = 1200;
    private const CANVAS_HEIGHT = 800;
    private const CANVAS_COLOR  = 'FFFFFF00';

    // Polaroid images configuration
    private const POLAROID_ANGLE         = 10;
    private const POLAROID_WIDTH         = 300;
    private const POLAROID_HEIGHT        = 280;
    private const POLAROID_BORDER        = 20;
    private const POLAROID_BOTTOM_MARGIN = 40;
    private const POLAROID_COLOR         = 'FFF';

    // Polaroid distancing configuration
    private const MIN_DISTANCE = 200;
    private const MAX_ATTEMPTS = 30;

    protected string $cacheDir;

    /**
     * Constructor - Initializes cache
     */
    public function __construct()
    {
        $this->cacheDir = storage_path('app/public/thumbnails');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Returns a large render of the provided photo
     */
    public function render(Photo $photo): BinaryFileResponse|BinaryStreamResponse
    {
        $fullPath = $this->getFilePath($photo);

        if (!config('settings.downscale_renders'))
        {
            return Response::file($fullPath);
        }

        return $this->downScaledRender($fullPath);
    }

    /**
     * Enforces downloading of the original file of the provided photo
     */
    public function download(Photo $photo): BinaryFileResponse
    {
        $fullPath = $this->getFilePath($photo);

        return Response::download($fullPath);
    }

    /**
     * Returns a downscaled version of the provided photo
     */
    public function thumbnail(Photo $photo, int $maxDimensions = 500, ?float $ratio = null): BinaryFileResponse
    {
        $fullPath = $this->getFilePath($photo);

        $cacheName = md5_file($fullPath) . $maxDimensions . ($ratio ? '_ratio' . $ratio : '');
        if ($image = $this->retrieveFromCache($cacheName)) {
            return $image;
        }

        $manager = $this->getImageManager();
        $image = $manager->read($fullPath);

        if ($ratio !== null)
        {
            $image = $this->cropToRatio($image, $ratio);
        }

        if ($image->width() > $maxDimensions || $image->height() > $maxDimensions) {
            $image = $this->resizeImage($image, $maxDimensions);
        }

        return $this->storeInCache($image, $cacheName);
    }

    /**
     * Returns an image file with the provided photos on it
     */
    public function collection(Collection $photos): BinaryStreamResponse
    {
        $fullPaths = [];
        foreach ($photos as $photo)
        {

            $fullPath = $this->getFilePath($photo);
            if (file_exists($fullPath))
            {
                $fullPaths[] = $fullPath;
            }

        }

        $positions = $this->generatePositions(
            new Dimension(self::CANVAS_WIDTH, self::CANVAS_HEIGHT),
            new Dimension(self::POLAROID_WIDTH, self::POLAROID_HEIGHT),
            count($fullPaths)
        );

        $manager = $this->getImageManager();
        $canvas = $manager->create(self::CANVAS_WIDTH, self::CANVAS_HEIGHT)->fill(self::CANVAS_COLOR);

		// TODO :: Make this configurable
		ini_set('memory_limit', '512M');
		
        foreach ($fullPaths as $index => $fullPath)
        {

            $image = $manager->read($fullPath)->cover(
                self::POLAROID_WIDTH - 2 * self::POLAROID_BORDER,
                self::POLAROID_HEIGHT - self::POLAROID_BOTTOM_MARGIN - 2 * self::POLAROID_BORDER,
                'center'
            );

            $polaroid = $manager->create(self::POLAROID_WIDTH, self::POLAROID_HEIGHT)->fill(self::POLAROID_COLOR);
            $polaroid->place($image, 'top-left', self::POLAROID_BORDER, self::POLAROID_BORDER);

            $angle = rand(-self::POLAROID_ANGLE, self::POLAROID_ANGLE);
            $rotated = $polaroid->rotate($angle, self::CANVAS_COLOR);

            $pos = $positions[$index];
            $canvas->place($rotated, 'top-left', $pos->x, $pos->y);
        }

        // TODO :: Store thumbnail for folders in cache (after resizing)
		$canvas = $this->resizeImage($canvas);
        return response((string) $canvas->toPng(), 200)->header('Content-Type', 'image/png');
    }

    /**
     * Returns blurhash for given photo
     */
    function blurhash(Photo $photo, $width = 4, $height = 3)
    {
        return Blurhash::encode($this->pixels($photo), $width, $height);
    }

    /**
     * Returns a downscaled render of the provided photo
     */
    private function downScaledRender(String $fullPath): BinaryFileResponse|BinaryStreamResponse
    {

        $cacheName = 'render_' . md5_file($fullPath) . self::RENDER_DIMS;
        if (config('settings.cache_renders') && $image = $this->retrieveFromCache($cacheName) )
        {
            return $image;
        }

        $manager = $this->getImageManager();
        $image = $manager->read($fullPath);

        if ($image->width() > self::RENDER_DIMS || $image->height() > self::RENDER_DIMS)
        {
            $image = $this->resizeImage($image, self::RENDER_DIMS);
        }

        if (config('settings.cache_renders'))
        {
             return $this->storeInCache($image, $cacheName);
        }

        return response((string) $image->encode(new JpegEncoder(80)))->header('Content-Type', 'image/jpeg');
    }

    /**
     * Retrieve an image from cache using the given name
     */
    private function retrieveFromCache(string $name): ?BinaryFileResponse
    {
        $fullPath = $this->getCachePath($name);
        if (!file_exists($fullPath)) {
            return null;
        }

        return Response::file($fullPath);
    }

    /**
     * Stores the given image in the cache under the given name
     */
    private function storeInCache(Image $image, string $name): BinaryFileResponse
    {
        $fullPath = $this->getCachePath($name);
        $image->save($fullPath, 100, 'jpg');

        return Response::file($fullPath);
    }

    /**
     * Returns the absolute path to the given photo
     */
    private function getFilePath(Photo $photo): string
    {
        $fullPath = resource_path($photo->folder->path . DIRECTORY_SEPARATOR . $photo->filename);

        if (!file_exists($fullPath)) {
            abort(404, 'Photo file not found.');
        }

        return $fullPath;
    }

    /**
     * Returns the absolute path to the given file in the cache
     */
    private function getCachePath(string $name): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . $name . '.jpg';
    }

    /**
     * Returns a downsaled version of the given image
     */
    private function resizeImage(Image $image, int $maxDimension = 500): Image
    {
        $ratio = min($maxDimension / $image->width(), $maxDimension / $image->height());

        $newWidth = (int) round($image->width() * $ratio);
        $newHeight = (int) round($image->height() * $ratio);

        return $image->resize($newWidth, $newHeight);
    }

    /**
     * Crops an image to a given width / height ratio (from the center)
     */
    protected function cropToRatio(Image $image, float $ratio): Image
    {
        $currentWidth  = $image->width();
        $currentHeight = $image->height();
        $currentRatio  = $currentWidth / $currentHeight;

        if ($currentRatio > $ratio)
        {
            $newWidth = intval($currentHeight * $ratio);
            $x = intval(($currentWidth - $newWidth) / 2);
            $image->crop($newWidth, $currentHeight, $x, 0);
        }
        elseif ($currentRatio < $ratio)
        {
            $newHeight = intval($currentWidth / $ratio);
            $y = intval(($currentHeight - $newHeight) / 2);
            $image->crop($currentWidth, $newHeight, 0, $y);
        }

        return $image;
    }

    /**
     * Get (downscaled) pixel matrix for given photo
     */
    private function pixels(Photo $photo): array
    {
        $manager  = $this->getImageManager();
        $fullPath = $this->getFilePath($photo);

        $image  = $manager->read($fullPath);
        $image  = $this->resizeImage($image, 100);
        
        $pixels = [];
        for ($y = 0; $y < $image->height(); $y++)
        {
            $row = [];
            for ($x = 0; $x < $image->width(); ++$x)
            {
                $color = $image->pickColor($x, $y);
                $row[] = [$color->red()->toInt(), $color->green()->toInt(), $color->blue()->toInt()];
            }

            $pixels[] = $row;
        }

        return $pixels;
    }

    /**
     * Generates random (spread) positions for items on a canvas
     */
    private function generatePositions(Dimension $canvas, Dimension $item, int $count = 10): array
    {

        $positions = [];

        for ($i = 0; $i < $count; $i++)
        {
            $placed = false;

            for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++)
            {

                $pos = $this->getRandomPos($canvas->width, $canvas->height, $item->width, $item->height);

                if ($this->isFarEnough($pos, $positions, self::MIN_DISTANCE))
                {
                    $positions[] = $pos;
                    $placed = true;
                    break;
                }

            }

            if (!$placed)
            {
                $positions[] = $this->getRandomPos($canvas->width, $canvas->height, $item->width, $item->height);
            }

        }

        return $positions;
    }

    /**
     * Returns a random position (top / left) for item placement
     */
    private function getRandomPos(int $cw, int $ch, int $pw, int $ph, $margin = 25): Position
    {
		
        $x = rand($margin, $cw - $pw- $margin);
        $y = rand($margin, $ch - $ph - $margin);

        return new Position($x, $y, $pw, $ph);
    }

    /**
     * Checks whether the given position is placed at a given distance from a given list of positions
     */
    private function isFarEnough(Position $newPos, array $positions, int $minDist): bool
    {
        foreach ($positions as $pos)
        {
            if ($newPos->distanceTo($pos) < $minDist)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns image manager based on available drivers
     */
    private function getImageManager(): ImageManager
    {
        $driver = class_exists('Imagick') ? new ImagickDriver() : new GdDriver();
        return new ImageManager($driver);
    }

}
