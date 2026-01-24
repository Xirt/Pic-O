<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Http\Resources\ShareTokenResource;
use App\Models\ShareToken;
use App\Models\Album;

/**
 * Handles Share Token management via API endpoints.
 *
 * Provides:
 *  - Share token creation for albums.
 *  - Share token revocation.
 *  - Album token listing (unused).
 *
 * Routes:
 *  - POST   /api/tokens
 *  - DELETE /api/tokens/{id}
 */
class ShareTokenController extends Controller
{
    /**
     * Retrieve one or more Tokens
     * UNUSED
     */
    public function index(Album $album): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ShareToken::class);
        $this->authorize('view', $album);

        $tokens = ShareToken::query()
            ->where('album_id', $album->id)
            ->get();

        return ShareTokenResource::collection($tokens);
    }

    /**
     * Create a new Share Token for a specific Album
     *
     * @param Request $request
     *
     * @return ShareTokenResource
     */
    public function store(Request $request): ShareTokenResource
    {
        $this->authorize('create', ShareToken::class);

        $request->validate([
            'album_id'   => 'required|exists:albums,id',
            'expires_at' => 'nullable|date',
        ]);

        $token = ShareToken::generateForAlbum(
            $request->album_id,
            $request->expires_at ? new DateTime($request->expires_at) : null,
        );

        return new ShareTokenResource($token);
    }

    /**
     * Update a given Share Token
     *
     * @param Request    $request
     * @param ShareToken $shareToken
     *
     * @return ShareTokenResource
     */
    public function update(Request $request, ShareToken $token): ShareTokenResource
    {
        $this->authorize('update', $token);

        $validated = $request->validate([
            'expires_at' => 'nullable|date',
        ]);

        if (!empty($validated['expires_at']))
        {
            $validated['expires_at'] = Carbon::parse($validated['expires_at'])->endOfDay();
        }

        $token->fill($validated);
        $token->save();

        return new ShareTokenResource($token);
    }

    /**
     * Delete a given Share Token
     *
     * @param ShareToken $token
     *
     * @return JsonResponse
     */
    public function destroy(ShareToken $token): JsonResponse
    {
        $this->authorize('delete', $token);

        $token->delete();

        return response()->json(['message' => 'Token revoked']);
    }
}
