<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Photo;
use App\Services\PhotoService;

class PhotoController extends Controller
{                                        
    /**
     * Show one or more Photos
     * GET /
     */
    public function index(Request $request)
    {
        if (!$request->user())
        {
            return redirect()->route('login');
        }

        $this->authorize('viewAny', Photo::class);

        return view('pages.timeline');
    }

    /**
     * Show a given Photo
     * GET /photos/{id}
     */
    public function showRender(Photo $photo, PhotoService $photoService)
    {
        $this->authorize('view', $photo);

        return $photoService->render($photo);
    }

    /**
     * Show a thumbnail version of a given Photo
     * GET /photos/{id}/thumbnail
     */
    public function showThumbnail(Photo $photo, PhotoService $photoService)
    {
        $this->authorize('view', $photo);
        
        return $photoService->thumbnail($photo);
    }

    /**
     * Download a given Photo
     * GET /photos/{id}/download
     */
    public function download(Photo $photo, PhotoService $photoService)
    {
        $this->authorize('view', $photo);

        return $photoService->download($photo);
    }
}
