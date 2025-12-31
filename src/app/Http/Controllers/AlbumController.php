<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManagerStatic as Image;

use App\Models\Album;
use App\Services\PhotoService;

/**
 * Handles web display of Albums.
 *
 * Provides:
 *  - Listing of albums
 *  - Viewing a single album
 *  - Generating album thumbnails and previews
 *
 * Routes:
 *  - GET /albums                -> index()
 *  - GET /albums/{id}           -> show()
 *  - GET /albums/{id}/thumbnail -> showThumbnail()
 *  - GET /albums/{id}/preview   -> showPreview()
 */
class AlbumController extends Controller
{
    private const DEFAULT_COVER_SRC = 'images/default_cover.png';

    private const DEFAULT_PREVIEW_SRC = 'images/default_preview.png';

    private const COVER_MAX_DIM = 500;

    private const PREVIEW_MAX_DIM = 750;

    /**
     * Show the albums listing page.
     *
     * @return View
     */
    public function index(): View
    {
        $this->authorize('viewAny', Album::class);

        return view('pages.albums');
    }

    /**
     * Show a specific Album page.
     *
     * @param Album $album
     *
     * @return View
     */
    public function show(Album $album): View
    {
        $this->authorize('view', $album);

        $album->recordImpression();

        return view('pages.album', compact('album'));
    }

    /**
     * Show the cover image for a given Album.
     *
     * @param Album $album
     * @param PhotoService $photoService
     *
     * @return Response
     */
    public function showCover(Album $album, PhotoService $photoService): SymfonyResponse
    {
        $this->authorize('view', $album);

        $photo = $album->coverPhoto()->with('folder')->first();
        if (!$photo && !($photo = $album->photos()->with('folder')->inRandomOrder()->first()))
        {
            $src = public_path(self::DEFAULT_COVER_SRC);
            return $this->getDefaultCover($src, self::COVER_MAX_DIM, self::COVER_MAX_DIM);
        }

        return $photoService->thumbnail($photo, self::COVER_MAX_DIM);
    }

    /**
     * Show a larger preview image for a given Album.
     *
     * @param Album $album
     * @param PhotoService $photoService
     *
     * @return Response
     */
    public function showPreview(Album $album, PhotoService $photoService): SymfonyResponse
    {
        $this->authorize('view', $album);

        $photo = $album->coverPhoto()->with('folder')->first();
        if (!$photo && !($photo = $album->photos()->with('folder')->inRandomOrder()->first()))
        {
            $src = public_path(self::DEFAULT_PREVIEW_SRC);
            return $this->getDefaultCover($src, self::PREVIEW_MAX_DIM, self::PREVIEW_MAX_DIM * 4/3);
        }

        return $photoService->thumbnail($photo, self::PREVIEW_MAX_DIM, 4/3);
    }

    /**
     * Returns the default Album cover image using given dimensions
     *
     * @param string $src
     * @param int    $width
     * @param int    $height
     *
     * @return Response
     */
    private function getDefaultCover($src, $width = 500, $height = 500): Response
    {
        $driver = class_exists('Imagick') ? new ImagickDriver() : new GdDriver();

        $manager = new ImageManager($driver);
        $image = $manager->read($src)->cover(
            $width, $height, 'center'
        );

        return response((string) $image->toPng(), 200)->header('Content-Type', 'image/png');

    }
}
