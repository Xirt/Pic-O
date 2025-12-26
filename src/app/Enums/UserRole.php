<?php

namespace App\Enums;

/**
 * Defines the roles a user can have within the application.
 */
enum UserRole: string
{
    case USER  = 'user';
    case ADMIN = 'admin';
}
