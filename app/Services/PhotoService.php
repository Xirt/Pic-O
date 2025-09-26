<?php

namespace App\Services;

use App\Models\Photo;
use App\ValueObjects\Position;
use App\ValueObjects\Dimension;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Collection;                   

class PhotoService
{
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

    public function __construct()
    {
        $this->cacheDir = storage_path('app/public/thumbnails');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function render(Photo $photo): Response
    {
        $fullPath = $this->getFullPath($photo);

        return Response::file($fullPath);
    }

    public function download(Photo $photo): BinaryFileResponse
    {
        $fullPath = $this->getFullPath($photo);

        return Response::download($fullPath);
    }

    public function thumbnail(Photo $photo, int $maxDimensions = 500): BinaryFileResponse
    {
        $fullPath = $this->getFullPath($photo);

        $cacheName = md5_file($fullPath) . $maxDimensions;
        if ($image = $this->retrieveFromCache($cacheName)) {
            return $image;
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($fullPath);

        if ($image->width() > $maxDimensions || $image->height() > $maxDimensions) {
            $image = $this->resizeImage($image, $maxDimensions);
        }

        return $this->storeInCache($image, $cacheName);
    }

    public function collection(Collection $photos): \Illuminate\Http\Response
    {
        $fullPaths = [];
        foreach ($photos as $photo)
        {

            $fullPath = $this->getFullPath($photo);
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

        $manager = new ImageManager(new Driver());
        $canvas = $manager->create(self::CANVAS_WIDTH, self::CANVAS_HEIGHT)->fill(self::CANVAS_COLOR);

        foreach ($fullPaths as $index => $fullPath)
        {

            $image = $manager->read($fullPath)->resize(
                self::POLAROID_WIDTH - 2 * self::POLAROID_BORDER,
                self::POLAROID_HEIGHT - self::POLAROID_BOTTOM_MARGIN - 2 * self::POLAROID_BORDER,
                function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }

            );

            $polaroid = $manager->create(self::POLAROID_WIDTH, self::POLAROID_HEIGHT)->fill(self::POLAROID_COLOR);
            $polaroid->place($image, 'top-left', self::POLAROID_BORDER, self::POLAROID_BORDER);

            $angle = rand(-self::POLAROID_ANGLE, self::POLAROID_ANGLE);
            $rotated = $polaroid->rotate($angle, self::CANVAS_COLOR);

            $pos = $positions[$index];
            $canvas->place($rotated, 'top-left', $pos->x, $pos->y);
        }

        // TODO :: Store thumbnail for folders in cache (after resizing)
        return response((string) $canvas->toPng(), 200)->header('Content-Type', 'image/png');
    }

    private function getFullPath(Photo $photo): string
    {
        $fullPath = resource_path($photo->folder->path . DIRECTORY_SEPARATOR . $photo->filename);

        if (!file_exists($fullPath)) {
            abort(404, 'Photo file not found.');
        }

        return $fullPath;
    }

    private function retrieveFromCache(string $name): ?BinaryFileResponse
    {
        $fullPath = $this->getCachePath($name);
        if (!file_exists($fullPath)) {
            return null;
        }

        return Response::file($fullPath);
    }

    private function storeInCache($image, string $name): BinaryFileResponse
    {
        $fullPath = $this->getCachePath($name);
        $image->save($fullPath, 100, 'jpg');

        return Response::file($fullPath);
    }

    private function getCachePath(string $name): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . $name . '.jpg';
    }

    private function resizeImage(\Intervention\Image\Image $image, int $maxDimension): \Intervention\Image\Image
    {
        $ratio = min($maxDimension / $image->width(), $maxDimension / $image->height());

        $newWidth = (int) round($image->width() * $ratio);
        $newHeight = (int) round($image->height() * $ratio);

        return $image->resize($newWidth, $newHeight);
    }

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

    private function getRandomPos(int $cw, int $ch, int $pw, int $ph): Position
    {
        $x = rand(0, $cw - $pw);
        $y = rand(0, $ch - $ph);

        return new Position($x, $y, $pw, $ph);
    }

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

}
