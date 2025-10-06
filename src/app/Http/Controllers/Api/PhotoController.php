<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Folder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\PhotoResource;

class PhotoController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Photo::class, 'photo');
    }

    // GET /photos
    public function index(): AnonymousResourceCollection
    {
        $photos = Photo::orderBy('taken_at', 'desc')
                       ->paginate(50);

        return PhotoResource::collection($photos);
    }

    // GET /api/albums/{album}/photos
    public function byAlbum(Album $album): AnonymousResourceCollection
    {
        $photos = $album->photos()
            ->orderBy('taken_at', 'desc')
            ->paginate(50);

        return PhotoResource::collection($photos);
    }

    // GET /api/folders/{folder}/photos
    public function byFolder(Folder $folder): AnonymousResourceCollection
    {
        $photos = $folder->photos()
            ->orderBy('taken_at', 'desc')
            ->paginate(50);

        return PhotoResource::collection($photos);
    }

    // GET /api/photos/{photo}
    public function show(Photo $photo): PhotoResource
    {
        return new PhotoResource($photo);
    }
}
