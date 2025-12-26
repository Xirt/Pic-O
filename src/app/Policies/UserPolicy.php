<?php

namespace App\Policies;                 

use App\Models\User;
use App\Enums\UserRole;

/**
 * Policy class for managing access control to a given model.
 *
 * This class defines authorization rules for actions such as
 * viewing, creating, updating, deleting, restoring, or force-deleting
 * the associated model. Methods typically receive a User instance
 * and optionally the model instance to determine permissions.
 */
class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param User $user The authenticated user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can view the specific model.
     *
     * @param User $user  The authenticated user
     * @param User $model The model to view
     *
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        return $user && ($user->id === $model->id || in_array($user->role, [UserRole::ADMIN]));
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
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can update the specific model.
     *
     * @param User|null $user  The authenticated user, if any
     * @param User      $model The model to view
     *
     * @return bool
     */
    public function update(?User $user, User $model): bool
    {
        return $user && ($user->id === $model->id || in_array($user->role, [UserRole::ADMIN]));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, User $model): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, User $model): bool
    {
        return false;
    }
}
