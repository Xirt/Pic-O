<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ShareToken;
use App\Enums\UserRole;

/**
 * Policy class for managing access control to a given model.
 *
 * This class defines authorization rules for actions such as
 * viewing, creating, updating, deleting, restoring, or force-deleting
 * the associated model. Methods typically receive a User instance
 * and optionally the model instance to determine permissions.
 */
class ShareTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param User|null $user The authenticated user, if any
     *
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return (bool) $user;
    }

    /**
     * Determine whether the user can view the specific model.
     *
     * @param User|null  $user  The authenticated user, if any
     * @param ShareToken $token The model to view
     *
     * @return bool
     */
    public function view(?User $user, ShareToken $token): bool
    {
        return (bool) $user;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User|null $user The authenticated user, if any
     *
     * @return bool
     */
    public function create(?User $user): bool
    {
        return $user && in_array($user->role, [UserRole::USER, UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can update the specific model.
     *
     * @param User|null  $user  The authenticated user, if any
     * @param ShareToken $token The model to update
     *
     * @return bool
     */
    public function update(?User $user, ShareToken $token): bool
    {
        return $user && in_array($user->role, [UserRole::USER, UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can delete the specific model.
     *
     * @param User|null  $user  The authenticated user, if any
     * @param ShareToken $token The model to delete
     *
     * @return bool
     */
    public function delete(?User $user, ShareToken $token): bool
    {
        return $user && in_array($user->role, [UserRole::USER, UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can restore the specific model.
     *
     * @param User|null  $user  The authenticated user, if any
     * @param ShareToken $token The model to restore
     *
     * @return bool
     */
    public function restore(?User $user, ShareToken $token): bool
    {
        return $user && in_array($user->role, []);
    }

    /**
     * Determine whether the user can permanently delete the specific model.
     *
     * @param User|null  $user  The authenticated user, if any
     * @param ShareToken $token The model to permanently delete
     *
     * @return bool
     */
    public function forceDelete(?User $user, ShareToken $token): bool
    {
        return $user && in_array($user->role, []);
    }
}
