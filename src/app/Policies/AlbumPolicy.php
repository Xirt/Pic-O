<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Album;
use App\Enums\UserRole;

class AlbumPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return (bool) $user;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Album $album): bool
    {
        $token = request()->share_token;
        return ($user || ($token && $token->album_id === $album->id && !$token->isExpired()));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Album $album): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Album $album): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Album $album): bool
    {
        return $user && in_array($user->role, []);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Album $album): bool
    {
        return $user && in_array($user->role, []);
    }
}
