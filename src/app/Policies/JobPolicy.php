<?php

namespace App\Policies;
                                         
use App\Models\Job;
use App\Models\User;
use App\Enums\UserRole;

class JobPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Job $job): bool
    {
        return $user && in_array($user->role, [UserRole::ADMIN]);
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
    public function update(?User $user, Job $job): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Job $job): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Job $job): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Job $job): bool
    {
        return false;
    }
}
