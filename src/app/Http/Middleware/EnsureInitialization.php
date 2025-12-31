<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Setting;

/**
 * Middleware to ensure the application is initialized before accessing other routes.
 */
class EnsureInitialization
{
    /**
     * Handle an incoming request and checks for authentication via tokens.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure(Request): Response $next The next middleware callback.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('settings.setup_completed', 0) && !$request->is('setup*'))
        {
            return redirect()->route('setup.show');
        }

        return $next($request);
    }
}
