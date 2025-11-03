<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Folder;

class PhotoController extends Controller
{
    /**
     * Retrieve one or more Photos
     * GET /api/photos
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Photo::class);

        $photos = Photo::orderByRaw('taken_at IS NULL, taken_at DESC')
                       ->paginate(50);

        return PhotoResource::collection($photos);
    }

    /**
     * Retrieve Photos for a specific Album
     * GET /api/albums/{id}/photos
     */
    public function byAlbum(Album $album): AnonymousResourceCollection
    {
        $this->authorize('view', $album);

        $photos = $album->photos()
            ->orderByRaw('taken_at IS NULL, taken_at ASC')
            ->paginate(50);

        return PhotoResource::collection($photos);
    }

    /**
     * Retrieve Photos for a specific Folder
     * GET /api/folders/{id}/photos
     */
    public function byFolder(Folder $folder): AnonymousResourceCollection
    {
        $this->authorize('view', $folder);

        $photos = $folder->photos()
            ->orderByRaw('taken_at IS NULL, taken_at ASC')  
            ->paginate(50);

        return PhotoResource::collection($photos);
    }

    /**
     * Retrieve a specific Photo
     * GET /api/photos/{id}
     */
    public function show(Photo $photo): PhotoResource
    {
        $this->authorize('view', $photo);

        return new PhotoResource($photo);
    }

    /**
     * Record an impression for a photo
     * GET /api/photos/{id}/impression
     */
    public function recordImpression(Photo $photo): Response
    {
        $this->authorize('view', $photo);

        $photo->recordImpression();

        return response()->noContent();
    }
}
