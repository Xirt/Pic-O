<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

use App\Enums\UserRole;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Retrieve one or more Users
     * GET /api/users
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::all();

        return UserResource::collection($users);
    }

    /**
     * Retrieve a specific User
     * GET /api/users/{id}
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    /**
     * Create a new User
     * POST /api/users
     */
    public function store(Request $request)
    {
        $this->authorize('store', User::class);

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
            ->setStatusCode(201)
            ->response();
    }

    /**
     * Update a given User
     * PUT /api/users/{id}
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => ['sometimes', new Enum(UserRole::class)],
        ]);

        // Prevent current admin from downgrading themselves
        if (isset($validated['role']) && $user->id === $request->user()->id && $user->role === 'admin' && $validated['role'] !== 'admin')
        {
            return response()->json([
                'message' => 'You cannot downgrade your own admin role.',
            ], 403);
        }

        if (isset($validated['password']))
        {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return (new UserResource($user))
            ->additional(['message' => 'User updated successfully.'])
            ->setStatusCode(200)
            ->response();
    }

    /**
     * Delete a given User
     * DELETE /api/users/{id}
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

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
}