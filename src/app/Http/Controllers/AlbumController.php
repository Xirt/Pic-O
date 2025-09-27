<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Services\PhotoService;

class AlbumController extends Controller
{
    // GET /albums
    public function index()
    {
        return view('pages.albums');
    }

    // GET /albums/{album}
    public function show(Album $album)
    {
        return view('pages.album', compact('album'));
    }

    // GET /albums/{album}/thumbnail
    public function showThumbnail(Album $album, PhotoService $photoService)
    {
        // TODO :: Create default thumbnail in case of no album cover
        if (!($photo = $album->coverPhoto()->with('folder')->first())) {
            abort(404, 'No cover photo set');
        }

        return $photoService->thumbnail($photo);
    }
}
