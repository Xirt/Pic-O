<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Response;

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
 *  - GET /albums               -> index()
 *  - GET /albums/{id}          -> show()
 *  - GET /albums/{id}/thumbnail -> showThumbnail()
 *  - GET /albums/{id}/preview   -> showPreview()
 */
class AlbumController extends Controller
{
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
     * Show the thumbnail image for a given Album.
     *
     * @param Album $album
     * @param PhotoService $photoService
     *
     * @return Response
     */
    public function showThumbnail(Album $album, PhotoService $photoService): Response
    {
        $this->authorize('view', $album);

        // TODO :: Create default thumbnail in case of no album cover
        if (!($photo = $album->coverPhoto()->with('folder')->first())) {
            abort(404, 'No cover photo set');
        }

        return $photoService->thumbnail($photo);
    }

    /**
     * Show a larger preview image for a given Album.
     *
     * @param Album $album
     * @param PhotoService $photoService
     *
     * @return Response
     */
    public function showPreview(Album $album, PhotoService $photoService): Response
    {
        $this->authorize('view', $album);

        // TODO :: Create default thumbnail in case of no album cover
        if (!($photo = $album->coverPhoto()->with('folder')->first())) {
            abort(404, 'No cover photo set');
        }

        return $photoService->thumbnail($photo, 750, 4/3);
    }
}
