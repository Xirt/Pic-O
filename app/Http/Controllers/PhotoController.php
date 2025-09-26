<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Services\PhotoService;

class PhotoController extends Controller
{
    // GET /
    public function index()
    {
        return view('pages.timeline');
    }

    // GET /photos/{photo}/download
    public function download(Photo $photo, PhotoService $photoService)
    {
        return $photoService->download($photo);
    }

    // GET /photos/{photo}
    public function showRender(Photo $photo, PhotoService $photoService)
    {
        return $photoService->render($photo);
    }

    // GET /photos/{photo}/thumbnail
    public function showThumbnail(Photo $photo, PhotoService $photoService)
    {
        return $photoService->thumbnail($photo);
    }
}
