<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

use App\Enums\UserRole;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Album;

/**
 * Handles User management via API endpoints.
 *
 * Provides:
 *  - User listing and retrieval.
 *  - User creation and updates.
 *  - User deletion with safety checks.
 *
 * Routes:
 *  - GET    /api/users
 *  - GET    /api/users/{id}
 *  - POST   /api/users
 *  - PUT    /api/users/{id} 
 *  - DELETE /api/users/{id}
 *  - PUT    /api/users/{id}/albums/
 *  - PUT    /api/users/{id}/albums/{id}
 *  - DELETE /api/users/{id}/albums/
 *  - DELETE /api/users/{id}/albums/{id}
 */
class UserController extends Controller
{
    /**
     * Retrieve one or more Users
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::all();

        return UserResource::collection($users);
    }

    /**
     * Retrieve a specific User
     *
     * @param User $user
     *
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    /**
     * Create a new User
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);
        if ($response = $this->denyIfDemoMode())
        {
            return $response;
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => ['required', new Enum(UserRole::class)],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return (new UserResource($user))
            ->additional(['message' => 'User created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a given User
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        if ($response = $this->denyIfDemoMode())
        {
            return $response;
        }

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role'     => ['sometimes', new Enum(UserRole::class)],
        ]);

        $actingUser = $request->user();
        $isCurrentUser = $actingUser->id === $user->id;
        $actingUserIsAdmin = $actingUser->role === UserRole::ADMIN;

        if (isset($validated['role']))
        {

            if (!$actingUserIsAdmin && $validated['role'] !== $user->role->value)
            {
                return response()->json([
                    'message' => 'Only administrators may change user roles.',
                ], 403);
            }

            if ($isCurrentUser && $actingUserIsAdmin && $validated['role'] !== UserRole::ADMIN->value)
            {
                return response()->json([
                    'message' => 'You cannot downgrade your own admin role.',
                ], 403);
            }

        }

        if (!empty($validated['password']))
        {
            $validated['password'] = Hash::make($validated['password']);
        }
        else
        {
            unset($validated['password']);
        }

        $user->update($validated);

        return (new UserResource($user))
            ->additional(['message' => 'User updated successfully.' . ($user->role === 'Admin')])
            ->response()
            ->setStatusCode(200);

    }

    /**
     * Delete a given User
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);
        if ($response = $this->denyIfDemoMode())
        {
            return $response;
        }

        if ($request->user()->id === $user->id)
        {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }       

        // Prevent deletion if it would leave 0 admins
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1)
        {
            return response()->json([
                'message' => 'You cannot delete the last remaining admin.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ], 200);
    }

    /**
     * Get albums assigned to a specific user (for guests)
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function getAlbums(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $albums = $user->albums()->with(['coverPhoto'])->withCount('photos')->get();

        return response()->json([
            'data' => $albums,
        ], 200);
    }

    /**
     * Assign a single album to a user (for guests)
     *
     * @param Request $request
     * @param User    $user
     * @param Album   $album
     *
     * @return JsonResponse
     */
    public function assignAlbum(Request $request, User $user, Album $album): JsonResponse
    {
        $modifiedRequest = $request->merge(['album_ids' => [$album->id]]);
        return $this->assignAlbums($modifiedRequest, $user);
    }

    /**
     * Assign multiple albums to a user (for guests)
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function assignAlbums(Request $request, User $user): JsonResponse
    {
        if ($response = $this->validateAlbumManagement($request, $user))
        {
            return $response;
        }

        $validated = $request->validate([
            'album_ids'   => 'required|array',
            'album_ids.*' => 'required|integer|exists:albums,id',
        ]);

        // Get albums not already assigned to avoid redundant database entries
        $alreadyAssigned = $user->albums()->pluck('albums.id')->toArray();
        $newAlbums = array_diff($validated['album_ids'], $alreadyAssigned);

        if (empty($newAlbums))
        {
            return response()->json([
                'message' => 'All specified albums were already assigned.',
            ], 200);
        }

        $user->albums()->attach($newAlbums);

        return response()->json([
            'message' => count($newAlbums) . ' new album(s) assigned successfully.',
        ], 200);
    }

    /**
     * Remove a single album from a user (for guests)
     *
     * @param Request $request
     * @param User    $user
     * @param Album   $album
     *
     * @return JsonResponse
     */
    public function removeAlbum(Request $request, User $user, Album $album): JsonResponse
    {
        $modifiedRequest = $request->merge(['album_ids' => [$album->id]]);
        return $this->removeAlbums($modifiedRequest, $user);
    }

    /**
     * Remove multiple albums from a user (for guests)
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function removeAlbums(Request $request, User $user): JsonResponse
    {
        if ($response = $this->validateAlbumManagement($request, $user))
        {
            return $response;
        }

        $validated = $request->validate([
            'album_ids'   => 'required|array',
            'album_ids.*' => 'required|integer|exists:albums,id',
        ]);

        $user->albums()->detach($validated['album_ids']);

        return response()->json([
            'message' => count($validated['album_ids']) . ' album(s) removed successfully.',
        ], 200);
    }

    /**
     * Validate authorization and permissions for album management
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse|null
     */
    private function validateAlbumManagement(Request $request, User $user): ?JsonResponse
    {
        $this->authorize('update', $user);
        
        if ($request->user()->role !== UserRole::ADMIN)
        {
            return response()->json([
                'message' => 'Only administrators can manage album assignments.',
            ], 403);
        }

        return $this->denyIfDemoMode();
    }

    /**
     * Block mutating actions in demo environment.
     *
     * @return JsonResponse|null
     */
    private function denyIfDemoMode(): ?JsonResponse
    {
        if (config('settings.demo_environment', 0) == 1)
        {
            return response()->json([
                'message' => 'User management is disabled in demo mode.',
            ], 403);
        }

        return null;
    }
}
