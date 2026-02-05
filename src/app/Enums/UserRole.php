<?php

namespace App\Enums;

/**
 * Defines the roles a user can have within the application.
 */
enum UserRole: string
{
    case GUEST  = 'guest';
    case USER   = 'user';
    case ADMIN  = 'admin';
}
