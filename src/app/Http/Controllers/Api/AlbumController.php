<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

use App\Enums\AlbumType;
use App\Enums\DatePrecision;
use App\Http\Resources\AlbumResource;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Folder;

class AlbumController extends Controller
{
    /**
     * Retrieve one or more Albums
     * GET /api/albums
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Album::class);

        $albums = Album::with(['coverPhoto'])
           ->withCount('photos')
           ->orderBy('name', 'asc')
           ->paginate(10);

        return AlbumResource::collection($albums);
    }

    /**
     * Search for one or more Albums
     * GET /api/albums/search
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Album::class);

        $validated = $request->validate([
            'q'         => ['nullable', 'string', 'max:255'],
            'type'      => ['nullable', Rule::enum(AlbumType::class)],
            'order'     => ['nullable', Rule::in(['name', 'type', 'start_date', 'photos_count'])],
            'direction' => ['nullable', Rule::in(['ASC', 'DESC', 'asc', 'desc'])],
        ]);

        $query     = $validated['q'] ?? '';
        $type      = $validated['type'] ?? null;
        $order     = $validated['order'] ?? 'name';
        $direction = strtoupper($validated['direction'] ?? 'ASC');

        $albums = Album::with(['coverPhoto'])
            ->withCount('photos')
            ->when($query, fn($q) => $q->where('name', 'like', "%{$query}%"))
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy($order, $direction)
            ->paginate(10);

        return AlbumResource::collection($albums);
    }

    /**
     * Retrieve a specific Album
     * GET /api/albums/{id}
     */
    public function show(Album $album): AlbumResource
    {
        $this->authorize('view', $album);

        return new AlbumResource($album);
    }

    /**
     * Create a new Album
     * POST /api/albums/create
     */
    public function store(Request $request): AlbumResource
    {
        $this->authorize('create', Album::class);

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type'      => ['required', new Enum(AlbumType::class)],
        ])->validate();

        $photoIds = $this->getPhotoIds($request);
        return $this->createAlbum($validated['name'], $validated['type'], $photoIds);
    }

    /**
     * Create a new Album using a specific Folder
     * POST /api/albums/from-folder
     */
    public function storeFromFolder(Request $request): AlbumResource
    {
        $this->authorize('create', Album::class);

        $validated = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'type'      => ['required', new Enum(AlbumType::class)],
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
        return $this->createAlbum($validated['name'], $validated['type'], $photoIds);
    }

    /**
     * Update a given Album
     * PATCH /api/albums/{id}
     */
    public function update(Request $request, Album $album): AlbumResource
    {
        $this->authorize('update', $album);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'type'           => ['sometimes', new Enum(AlbumType::class)],
            'photo_id'       => 'sometimes|integer|exists:photos,id',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'date_precision' => ['sometimes', new Enum(DatePrecision::class)],
        ]);

        $album->fill($validated);

        if (!isset($validated['date_precision']))
        {
            $album->date_precision = $album->start_date && $album->end_date
                ? ($album->start_date->equalTo($album->end_date) ? DatePrecision::DAY : DatePrecision::RANGE)
                : DatePrecision::UNKNOWN;
        }

        $album->save();

        return new AlbumResource($album);
    }

    /**
     * Add a given Photo to a given Album
     * PUT /api/albums/{id}/photos/{id}
     */
    public function addPhoto(Request $request, Album $album, Photo $photo): JsonResponse
    {
        $this->authorize('update', $album);

        $modifiedRequest = $request->merge(['pictures' => [$photo->id]]);

        return $this->addPhotos($modifiedRequest, $album);
    }

    /**
     * Add given Photos to a given Album
     * PUT /api/albums/{id}/photos
     */
    public function addPhotos(Request $request, Album $album): JsonResponse
    {
        $this->authorize('update', $album);

        $photoIds = $this->getPhotoIds($request);
        $alreadyAttached = $album->photos()->whereIn('photos.id', $photoIds)->pluck('photos.id')->toArray();
        $album->photos()->attach(array_diff($photoIds, $alreadyAttached));

        return response()->json(['status' => 'ok']);
    }

    /**
     * Remove a given Photo from a given Album
     * DELETE /api/albums/{id}/photos/{id}
     */
    public function removePhoto(Album $album, Photo $photo)
    {
        $this->authorize('update', $album);

        $album->photos()->detach($photo->id);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Delete a given Album
     * DELETE /api/albums/{album}
     */
    public function destroy(Album $album): JsonResponse
    {
        $this->authorize('delete', $album);

        $album->delete();

        return response()->json(['message' => 'Album deleted']);
    }

    private function createAlbum(String $name, String $type, array $photoIds): AlbumResource
    {
        $album = Album::create([
            'name'     => $name,
            'type'     => $type,
            'photo_id' => $this->getRandomCover($photoIds),
        ]);

        $album->photos()->attach($photoIds);
        $album->fill($this->getDateRange($album))->save();

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

    private function getDateRange(Album $album): array
    {
        return $album->photos()
            ->selectRaw('MIN(taken_at) as start_date, MAX(taken_at) as end_date')
            ->first()
            ->toArray();
    }

    private function getRandomCover($list): ?int
    {
        return $list ? $list[array_rand($list)] : null;
    }
}
