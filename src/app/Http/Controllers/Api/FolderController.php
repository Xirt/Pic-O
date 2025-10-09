<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Models\Folder;
use App\Http\Resources\FolderResource;

class FolderController extends Controller
{
    /**
     * Retrieve one or more Folders
     * GET /api/folders
     */
    public function index(): FolderResource
    {
        $folder = Folder::whereNull('parent_id')->firstOrFail();

        $this->authorize('view', $folder);

        return new FolderResource($folder);
    }

    /**
     * Search for one or more Folders
     * GET /api/folders/search
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', $folder);

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

    /**
     * Retrieve a specific Folder
     * GET /api/folders/{id}
     */
    public function show(int $folderId): FolderResource
    {
        $folder = Folder::findOrFail($this->parseFolderId($folderId));

        $this->authorize('view', $folder);

        return new FolderResource($folder);
    }

    /**
     * Retrieve subfolders for a specific Folder
     * GET /api/folders/{id}/subfolders
     */
    public function subfolders(int $folderId): AnonymousResourceCollection
    {
        $folder = Folder::findOrFail($this->parseFolderId($folderId));

        $this->authorize('view', $folder);

        $subfolders = Folder::where('parent_id', $folder->id)
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
