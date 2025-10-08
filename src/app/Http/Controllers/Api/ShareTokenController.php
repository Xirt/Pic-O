<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\ShareTokenResource;
use App\Models\ShareToken;

class ShareTokenController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ShareToken::class, 'token');
    }

    public function index()
    {
        $query = ShareToken::query();

        if ($request->has('album_id'))
        {
            $query->where('album_id', $request->album_id);
        }

        return ShareTokenResource::collection($query->get());
    }

    public function store(Request $request): ShareTokenResource
    {
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

    public function revoke(ShareToken $id): JsonResponse
    {
        $token = ShareToken::findOrFail($id);
        $token->delete();

        return response()->json(['message' => 'Token revoked']);
    }
}
