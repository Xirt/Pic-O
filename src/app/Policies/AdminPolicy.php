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
class AdminPolicy
{
    /**
     * Determine if the user can access the admin dashboard.
     *
     * @param User|null $user The authenticated user, if any
     *
     * @return bool
     */
    public function access(?User $user): bool
    {                      
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }
}
