<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Response;

use App\Models\Photo;
use App\Models\Folder;
use App\Services\PhotoService;

/**
 * Handles web display of Folders.
 *
 * Provides:
 *  - Listing of folders
 *  - Generating folder thumbnails
 *
 * Routes:
 *  - GET /folders                -> index()
 *  - GET /folders/{id}/thumbnail -> thumbnail()
 */
class FolderController extends Controller
{          
    /**
     * Show the folders listing page.
     *
     * @return View
     */
    public function index(): View
    {
        $this->authorize('viewAny', Folder::class);

        return view('pages.folders');
    }

    /**
     * Show a thumbnail collection for a given Folder.
     *
     * @param int $folderId
     * @param PhotoService $photoService
     *
     * @return Response
     */
    public function thumbnail(int $folderId, PhotoService $photoService): Response
    {
        $this->authorize('viewAny', Folder::class);

        $photos = Photo::with("folder")->where('folder_id', $folderId)
            ->inRandomOrder()
            ->take(10)
            ->get();

        return $photoService->collection($photos);
    }
}
