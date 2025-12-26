<?php

namespace App\Policies;
                                         
use App\Models\Job;
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
class JobPolicy
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
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can view the specific model.
     *
     * @param User|null $user The authenticated user, if any
     * @param Job       $job  The model to view
     *
     * @return bool
     */
    public function view(?User $user, Job $job): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
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
     * @param User|null $user The authenticated user, if any
     * @param Job       $job  The model to update
     *
     * @return bool
     */
    public function update(?User $user, Job $job): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the specific model.
     *
     * @param User|null $user The authenticated user, if any
     * @param Job       $job  The model to delete
     *
     * @return bool
     */
    public function delete(?User $user, Job $job): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the specific model.
     *
     * @param User|null $user The authenticated user, if any
     * @param Job       $job  The model to restore
     *
     * @return bool
     */
    public function restore(?User $user, Job $job): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the specific model.
     *
     * @param User|null $user The authenticated user, if any
     * @param Job       $job  The model to permanently delete
     *
     * @return bool
     */
    public function forceDelete(?User $user, Job $job): bool
    {
        return false;
    }
}
