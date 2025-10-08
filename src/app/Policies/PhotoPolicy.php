<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Photo;
use App\Enums\UserRole;

class PhotoPolicy
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
    public function view(?User $user, Photo $photo): bool
    {
        $token = request()->token;     
        if (!$user && $token && !$token->isExpired()) {
            return $photo->albums()->where('albums.id', $token->album_id)->exists();
        }

        return (bool) $user;
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
    public function update(?User $user, Photo $photo): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Photo $photo): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Photo $photo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Photo $photo): bool
    {
        return false;
    }
}
