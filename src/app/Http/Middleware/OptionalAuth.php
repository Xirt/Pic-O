<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ShareToken;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
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

            $request->merge(['token' => NULL]);
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
