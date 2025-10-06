<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\ShareToken;

class ShareTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tokenValue = $request->query('token') ?? $request->bearerToken();

        if ($tokenValue)
        {
            $token = ShareToken::where('token', $tokenValue)->first();

            if ($token && !$token->isExpired())
            {
                $request->merge(['share_token' => $token]);
                return $next($request);
            }

            return response()->json(['message' => 'Invalid or expired token'], 403);
        }

        return $next($request);
    }
}
