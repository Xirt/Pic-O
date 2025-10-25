<?php

namespace App\Enums;

enum DatePrecision: string
{
    case DAY   = 'day';
    case MONTH = 'month';
    case YEAR  = 'year';
    case RANGE = 'range';
    case UNKNOWN = 'unknown';
}
