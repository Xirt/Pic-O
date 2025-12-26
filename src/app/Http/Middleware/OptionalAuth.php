<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use App\Models\ShareToken;

/**
 * Policy for handling optional authentication rules.
 *
 * This policy determines which actions are permitted for users
 * who may or may not be authenticated. Methods typically define
 * access to resources based on the presence of a user and their role.
 */
class OptionalAuth
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
        Auth::shouldUse('web');
                                     
        if ($tokenParam = $request->query('token') ?? $request->bearerToken())
        {

            $token = ShareToken::where('token', $tokenParam)->first();
            if ($token && !$token->isExpired())
            {
                $request->session()->regenerate();
                session(['token_id' => $token->id]);
                $request->merge(['token' => $token]);

                return $next($request);
            }

            $request->merge(['token' => null]);
            return $next($request);
        }

        if (session()->has('token_id'))
        {
            $token = ShareToken::find(session('token_id'));
            if ($token && !$token->isExpired())
            {
                $request->merge(['token' => $token]);
                return $next($request);
            }

            session()->forget('token_id');
        }

        return $next($request);
    }
}
