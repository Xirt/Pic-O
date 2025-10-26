<?php

namespace App\Enums;

enum AlbumType: string
{
    case HOLIDAY      = 'holiday';
    case EVENT        = 'event';
    case PERSONAL     = 'personal';
    case TRIP         = 'trip';
    case FAMILY       = 'family';
    case WORK         = 'work';
    case NATURE       = 'nature';
    case CELEBRATION  = 'celebration';
    case PET          = 'pet';
    case OTHER        = 'other';
}
