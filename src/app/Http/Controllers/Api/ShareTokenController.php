<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\ShareTokenResource;
use App\Models\ShareToken;

class ShareTokenController extends Controller
{
    /**
     * Retrieve one or more Tokens
     * UNUSED
     */
    public function index()
    {
        $this->authorize('viewAny', ShareToken::class);

        $query = ShareToken::query();
        if ($request->has('album_id'))
        {
            $query->where('album_id', $request->album_id);
        }

        return ShareTokenResource::collection($query->get());
    }

    /**
     * Create a new Token for a specific Album
     * POST /api/tokens
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
     * Delete a given Token
     * DELETE /api/tokens/{id}
     */
    public function revoke(ShareToken $token): JsonResponse
    {
        $this->authorize('delete', $token);

        $token->delete();

        return response()->json(['message' => 'Token revoked']);
    }
}
