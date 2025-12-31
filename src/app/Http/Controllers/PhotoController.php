<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;      
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use App\Models\Photo;
use App\Services\PhotoService;

/**
 * Handles web display of Photos.
 *
 * Provides:
 *  - Timeline view
 *  - Viewing, rendering, and downloading individual photos
 *  - Generating photo thumbnails
 *
 * Routes:
 *  - GET /                      -> index()
 *  - GET /photos/{id}           -> showRender()
 *  - GET /photos/{id}/thumbnail -> showThumbnail()
 *  - GET /photos/{id}/download  -> download()
 */
class PhotoController extends Controller
{                                        
    /**
     * Show the timeline page (list of photos).
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->user())
        {
            return redirect()->route('login');
        }

        $this->authorize('viewAny', Photo::class);

        return view('pages.timeline');
    }

    /**
     * Render a specific Photo page.
     *
     * @param Photo $photo
     * @param PhotoService $photoService
     * @return SymfonyResponse
     */
    public function showRender(Photo $photo, PhotoService $photoService): SymfonyResponse
    {
        $this->authorize('view', $photo);

        $photo->recordImpression();

        return $photoService->render($photo);
    }

    /**
     * Show a thumbnail for a given Photo.
     *
     * @param Photo $photo
     * @param PhotoService $photoService
     * @return SymfonyResponse
     */
    public function showThumbnail(Photo $photo, PhotoService $photoService): SymfonyResponse
    {
        $this->authorize('view', $photo);
        
        return $photoService->thumbnail($photo);
    }

    /**
     * Download a specific Photo.
     *
     * @param Photo $photo
     * @param PhotoService $photoService
     * @return SymfonyResponse
     */
    public function download(Photo $photo, PhotoService $photoService): SymfonyResponse
    {
        $this->authorize('view', $photo);

        $photo->recordDownload();

        return $photoService->download($photo);
    }
}
