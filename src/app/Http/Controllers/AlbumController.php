<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Services\PhotoService;

class AlbumController extends Controller
{
    /**
     * Show one or more Albums
     * GET /albums
     */
    public function index()
    {
        $this->authorize('viewAny', Album::class);

        return view('pages.albums');
    }

    /**
     * Show a given Album
     * GET GET /albums/{id}
     */
    public function show(Album $album)
    {
        $this->authorize('view', $album);

        return view('pages.album', [
            'sharedView' => (bool) request()->token,
            'album'      => $album,
        ]);
    }

    /**
     * Show thumbnail for a given Album
     * GET /albums/{id}/thumbnail
     */
    public function showThumbnail(Album $album, PhotoService $photoService)
    {
        $this->authorize('view', $album);

        // TODO :: Create default thumbnail in case of no album cover
        if (!($photo = $album->coverPhoto()->with('folder')->first())) {
            abort(404, 'No cover photo set');
        }

        return $photoService->thumbnail($photo);
    }
}
