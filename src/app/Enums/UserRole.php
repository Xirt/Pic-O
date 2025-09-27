<?php

namespace App\Enums;

enum UserRole: string
{
    case USER  = 'user';
    case ADMIN = 'admin';
}
