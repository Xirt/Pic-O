<?php

namespace App\Enums;

/**
 * Specifies the precision level of a date range.
 */
enum DatePrecision: string
{
    case DAY     = 'day';
    case MONTH   = 'month';
    case YEAR    = 'year';
    case RANGE   = 'range';
    case UNKNOWN = 'unknown';
}
