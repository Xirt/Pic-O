<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Base controller for the application.
 *
 * Provides:
 *  - Authorization helpers via AuthorizesRequests trait
 *
 * All other controllers extend this base controller.
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
}
