<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Folder;
use App\Services\PhotoService;       

class FolderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Folder::class, 'folder');
    }

    // GET /folders
    public function index()
    {
        return view('pages.folders');
    }

    // GET /folders/{folder}/thumbnail
    public function thumbnail(int $folderId, PhotoService $photoService)
    {
        $photos = Photo::with("folder")->where('folder_id', $folderId)
            ->inRandomOrder()
            ->take(10)
            ->get();

        return $photoService->collection($photos);
    }
}
