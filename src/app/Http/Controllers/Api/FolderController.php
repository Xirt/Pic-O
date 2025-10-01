<?php

namespace App\Http\Controllers\Api;

use App\Models\Folder;
use App\Http\Controllers\Controller;
use App\Http\Resources\FolderResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FolderController extends Controller
{
    // GET /api/folders
    public function index(): FolderResource
    {
        $rootFolder = Folder::whereNull('parent_id')->firstOrFail();

        return new FolderResource($rootFolder);
    }

    // GET /api/folders/search
    public function search(Request $request): AnonymousResourceCollection
    {
        $query = $request->query('q', '');

        if (Str::of($query)->trim()->length() < 2) {
            return response()->json([], 200);
        }

        $folders = Folder::query()
            ->where('path', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->paginate(10);

        return FolderResource::collection($folders);
    }

    // GET /api/folders/{folder}
    public function show(int $folderId): FolderResource
    {
        $folder = Folder::findOrFail($this->parseFolderId($folderId));

        return new FolderResource($folder);
    }

    // GET /api/folders/{folder}/subfolders
    public function subfolders(int $folderId): AnonymousResourceCollection
    {
        $subfolders = Folder::where('parent_id', $this->parseFolderId($folderId))
            ->orderBy('name', 'asc')
            ->paginate(50);

        return FolderResource::collection($subfolders);
    }

    private function parseFolderId(int $folderId): int
    {
        if ($folderId === 0) {

            $folder = Folder::whereNull('parent_id')->firstOrFail();
            return $folder?->id ?? 0;

        }

        return $folderId;
    }
}
