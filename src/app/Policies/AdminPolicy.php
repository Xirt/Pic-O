<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class AdminPolicy
{
    /**
     * Determine if the user can access the admin dashboard.
     */
    public function access(User $user): bool
    {                      
        return $user && in_array($user->role, [UserRole::ADMIN]);
    }
}
