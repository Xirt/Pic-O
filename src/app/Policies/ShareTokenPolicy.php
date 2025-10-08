<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ShareToken;
use App\Enums\UserRole;

class ShareTokenPolicy
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
    public function view(?User $user, ShareToken $album): bool
    {
        return (bool) $user;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user && in_array($user->role, [UserRole::USER, UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, ShareToken $album): bool
    {
        return $user && in_array($user->role, [UserRole::USER, UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, ShareToken $album): bool
    {
        return $user && in_array($user->role, [UserRole::USER, UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, ShareToken $album): bool
    {
        return $user && in_array($user->role, []);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, ShareToken $album): bool
    {
        return $user && in_array($user->role, []);
    }
}
