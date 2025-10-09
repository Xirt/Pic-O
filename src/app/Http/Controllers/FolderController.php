<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Folder;
use App\Services\PhotoService;       

class FolderController extends Controller
{          
    /**
     * Show one or more Folders
     * GET /folders
     */
    public function index()
    {
        $this->authorize('viewAny', Folder::class);

        return view('pages.folders');
    }

    /**
     * Show thumbnail for a given Folder
     * GET /folders/{id}/thumbnail
     */
    public function thumbnail(int $folderId, PhotoService $photoService)
    {
        $this->authorize('viewAny', Folder::class);

        $photos = Photo::with("folder")->where('folder_id', $folderId)
            ->inRandomOrder()
            ->take(10)
            ->get();

        return $photoService->collection($photos);
    }
}
