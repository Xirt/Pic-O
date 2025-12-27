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

/**
 * Handles Album management via API endpoints.
 *
 * Provides:
 *  - Album listing and searching.
 *  - Album creation and updates.
 *  - Photo attachment and detachment.
 *  - Folder-based album and photo operations.
 *
 * Routes:
 *  - GET    /api/albums
 *  - GET    /api/albums/search
 *  - GET    /api/albums/{id}
 *  - POST   /api/albums/create
 *  - POST   /api/albums/from-folder
 *  - PATCH  /api/albums/{id}
 *  - PUT    /api/albums/{id}/photos
 *  - PUT    /api/albums/{id}/photos/{id}
 *  - POST   /api/albums/{id}/photos/from-folder
 *  - DELETE /api/albums/{id}/photos
 *  - DELETE /api/albums/{id}/photos/{id}
 *  - DELETE /api/albums/{id}
 */
class AlbumController extends Controller
{
    /**
     * Retrieve one or more Albums
     *
     * @return AnonymousResourceCollection
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
     *
     * @param Request $request
     *
     * @return AnonymousResourceCollection
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
            ->when($query, fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%']))
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy($order, $direction)
            ->paginate(25);

        return AlbumResource::collection($albums);
    }

    /**
     * Retrieve a specific Album
     *
     * @param Album $album
     *
     * @return AlbumResource
     */
    public function show(Album $album): AlbumResource
    {
        $this->authorize('view', $album);

        $album->recordImpression();

        return new AlbumResource($album);
    }

    /**
     * Create a new Album
     *
     * @param Request $request
     *
     * @return AlbumResource
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
     *
     * @param Request $request
     *
     * @return AlbumResource
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
     *
     * @param Request $request
     * @param Album   $album
     *
     * @return AlbumResource
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
     *
     * @param Request $request
     * @param Album   $album
     * @param Photo   $photo
     *
     * @return JsonResponse
     */
    public function addPhoto(Request $request, Album $album, Photo $photo): JsonResponse
    {
        $this->authorize('update', $album);

        $modifiedRequest = $request->merge(['pictures' => [$photo->id]]);

        return $this->addPhotos($modifiedRequest, $album);
    }

    /**
     * Add given Photos to a given Album
     *
     * @param Request $request
     * @param Album   $album
     *
     * @return JsonResponse
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
     * Add Photos from a specific Folder to a given Album
     *
     * @param Request $request
     * @param Album   $album
     *
     * @return JsonResponse
     */
    public function addFromFolder(Request $request, Album $album): JsonResponse
    {
        $this->authorize('update', $album);

        $validated = $request->validate([
            'folder_id' => 'required|integer|min:1',
        ]);

        $folder = Folder::findOrFail($validated['folder_id']);

        $folderIds = collect([$folder->id]);
        if (!empty($validated['subdirectories']))
        {
            $subfolderIds = Folder::where('path', 'like', $folder->path . DIRECTORY_SEPARATOR . '%')->pluck('id');
            $folderIds = $folderIds->merge($subfolderIds);
        }

        $photoIds = Photo::whereIn('folder_id', $folderIds)->pluck('id');
        $album->photos()->syncWithoutDetaching($photoIds);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Remove a given Photo from a given Album
     *
     * @param Album $album
     * @param Photo $photo
     *
     * @return JsonResponse
     */
    public function removePhoto(Album $album, Photo $photo)
    {
        $this->authorize('update', $album);

        return $this->detachPhotos($album, [$photo->id]);
    }

    /**
     * Remove given Photos from a given Album
     *
     * @param Request $request
     * @param Album   $album
     *
     * @return JsonResponse
     */
    public function removePhotos(Request $request, Album $album)
    {
        $this->authorize('update', $album);

        return $this->detachPhotos($album, $this->getPhotoIds($request));
    }

    /**
     * Delete a given Album
     *
     * @param Album $album
     *
     * @return JsonResponse
     */
    public function destroy(Album $album): JsonResponse
    {
        $this->authorize('delete', $album);

        $album->delete();

        return response()->json(['message' => 'Album deleted']);
    }

    /**
     * Persist a new Album and attach Photos
     *
     * @param string $name
     * @param string $type
     * @param array  $photoIds
     *
     * @return AlbumResource
     */
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

    /**
     * Extract and validate Photo IDs from request
     *
     * @param Request $request
     *
     * @return array
     */
    private function getPhotoIds(Request $request): array
    {
        $data = [
            'pictures' => (array) $request->input('pictures'),
        ];

        $validated = Validator::make($data, [
            'pictures'   => ['required', 'array', 'min:1'],
            'pictures.*' => ['integer']
        ])->validate();

        return Photo::whereIn('id', $validated['pictures'])->pluck('id')->all();
    }

    /**
     * Detach Photos from an Album
     *
     * @param Album $album
     * @param array $photoIds
     *
     * @return JsonResponse
     */
    private function detachPhotos(Album $album, array $photoIds)
    {
        $album->photos()->detach($photoIds);

        return response()->json(['message' => 'Photo(s) removed']);
    }

    /**
     * Determine the date range for an Album
     *
     * @param Album $album
     *
     * @return array
     */
    private function getDateRange(Album $album): array
    {
        $start = $album->photos()->min('taken_at');
        $end   = $album->photos()->max('taken_at');

        return ['start_date' => $start, 'end_date' => $end];
    }

    /**
     * Select a random Photo ID for album cover
     *
     * @param array $list
     *
     * @return int|null
     */
    private function getRandomCover($list): ?int
    {
        return $list ? $list[array_rand($list)] : null;
    }
}
