<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\AlbumResource;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Folder;

class AlbumController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Album::class, 'album');
    }

    // GET /api/albums
    public function index(): AnonymousResourceCollection
    {
        $albums = Album::with(['coverPhoto'])
                       ->withCount('photos')
                       ->orderBy('name', 'asc')
                       ->paginate(10);

        return AlbumResource::collection($albums);
    }

    // GET /api/albums/search
    public function search(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', 'album');

        $query     = $request->query('q', '');
        $order     = $request->query('order', 'name');
        $direction = $request->query('direction', 'ASC');

        $validOrders = ['name', 'created_at', 'photos_count'];
        if (!in_array($order, $validOrders)) {
            $order = 'name';
        }

        $validDirections = ['ASC', 'DESC'];
        if (!in_array($direction, $validDirections)) {
            $direction = 'ASC';
        }

        $albums = Album::with(['coverPhoto'])
            ->withCount('photos')
            ->when($query, fn($q) => $q->where('name', 'like', "%{$query}%"))
            ->orderBy($order, $direction)
            ->paginate(10);

        return AlbumResource::collection($albums);
    }

    // GET /api/albums/{album}
    public function show(Album $album): AlbumResource
    {
        return new AlbumResource($album);
    }

    // POST /api/albums/create
    public function store(Request $request): AlbumResource
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ])->validate();

        $photoIds = $this->getPhotoIds($request);
        return $this->createAlbum($validated['name'], $photoIds);
    }

    // POST /api/albums/from-folder
    public function storeFromFolder(Request $request): AlbumResource
    {
        $this->authorize('create', 'album');

        $validated = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'folder_id' => 'required|integer|min:1',
        ])->validate();

        $folder = Folder::findOrFail($validated['folder_id']);

        $folderIds = [$folder->id];
        if (!empty($validated['subdirectories'])) {

            $folderIds = array_merge($folderIds, Folder::where('path', 'like', $folder->path . DIRECTORY_SEPARATOR . '%')
                ->pluck('id')
                ->toArray()
            );

        }

        $photoIds = Photo::whereIn('folder_id', $folderIds)->pluck('id')->toArray();
        return $this->createAlbum($validated['name'], $photoIds);
    }

    // PATCH /api/albums/{id}
    public function update(Request $request, Album $album): AlbumResource
    {
        // Validate the input: both fields optional
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'photo_id' => 'sometimes|integer|exists:photos,id',
        ]);

        // Update only if the fields are present in the request
        if (isset($validated['name'])) {
            $album->name = $validated['name'];
        }

        if (isset($validated['photo_id'])) {
            $album->photo_id = $validated['photo_id'];
        }

        $album->save();

        return new AlbumResource($album);
    }

    // PUT /api/albums/{id}/photos/{id}
    public function addPhoto(Request $request, Album $album, Photo $photo): JsonResponse
    {
        $this->authorize('update', $album);

        $modifiedRequest = $request->merge(['pictures' => [$photo->id]]);

        return $this->addPhotos($modifiedRequest, $album);
    }

    // PUT /api/albums/{id}/photos
    public function addPhotos(Request $request, Album $album): JsonResponse
    {
        $this->authorize('update', $album);

        $photoIds = $this->getPhotoIds($request);
        $alreadyAttached = $album->photos()->whereIn('photos.id', $photoIds)->pluck('photos.id')->toArray();

        $album->photos()->attach(array_diff($photoIds, $alreadyAttached));

        return response()->json(['status' => 'ok']);
    }

    // DELETE /api/albums/{id}/photos/{id}
    public function removePhoto(Album $album, Photo $photo)
    {
        $this->authorize('update', $album);

        $album->photos()->detach($photo->id);

        return response()->json(['status' => 'ok']);
    }

    // DELETE /api/albums/{album}
    public function destroy(Album $album): JsonResponse
    {
        $album->delete();

        return response()->json(['message' => 'Album deleted']);
    }

    private function createAlbum(String $name, array $photoIds): AlbumResource
    {
        $album = Album::create([
            'name'     => $name,
            'photo_id' => $this->getRandomCover($photoIds),
        ]);

        $album->photos()->attach($photoIds);

        return new AlbumResource($album);
    }

    private function getPhotoIds(Request $request): array
    {
        $data = $request->all();

        if (isset($data['pictures']) && !is_array($data['pictures'])) {
            $data['pictures'] = [$data['pictures']];
        }

        $validated = Validator::make($data, [
            'pictures'   => ['required', 'array'],
            'pictures.*' => ['integer']
        ])->validate();

        return Photo::whereIn('id', $validated['pictures'])->pluck('id')->toArray();
    }

    private function getRandomCover($list): ?int
    {
        return $list ? $list[array_rand($list)] : null;
    }
}
