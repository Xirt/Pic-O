<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Folder;

/**
 * Handles Photo retrieval and interaction via API endpoints.
 *
 * Provides:
 *  - Photo listing and pagination.
 *  - Album- and folder-based photo retrieval.
 *  - Single photo access.
 *  - Impression recording.
 *
 * Routes:
 *  - GET /api/photos
 *  - GET /api/photos/{id}
 *  - GET /api/photos/{id}/impression
 *  - GET /api/albums/{id}/photos
 *  - GET /api/folders/{id}/photos
 */
class PhotoController extends Controller
{
    /**
     * Retrieve one or more Photos
     *
     * @return AnonymousResourceCollection
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
     *
     * @param Album $album
     *
     * @return AnonymousResourceCollection
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
     *
     * @param Folder $folder
     *
     * @return AnonymousResourceCollection
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
     *
     * @param Photo $photo
     *
     * @return PhotoResource
     */
    public function show(Photo $photo): PhotoResource
    {
        $this->authorize('view', $photo);

        return new PhotoResource($photo);
    }

    /**
     * Record an impression for a Photo
     *
     * @param Photo $photo
     *
     * @return Response
     */
    public function recordImpression(Photo $photo): Response
    {
        $this->authorize('view', $photo);

        $photo->recordImpression();

        return response()->noContent();
    }
}
