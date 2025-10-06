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
        $token   = request()->share_token;
        $album   = request()->route('album');
        $albumId = $album instanceof \App\Models\Album ? $album->id : $album;

        return ($user || ($token && $token->album_id === $albumId && !$token->isExpired()));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Photo $photo): bool
    {
        $token = request()->share_token;
        return ($user || ($token && $token->album_id === $photo->album_id && !$token->isExpired()));
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
