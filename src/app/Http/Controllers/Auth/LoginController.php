<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Handles user authentication: login and logout.
 *
 * Provides:
 *  - Display of the login form.
 *  - Login attempt processing with rate limiting.
 *  - Logout and session invalidation.
 *
 * Routes:
 *  - GET  /login  -> index()
 *  - POST /login  -> login()
 *  - POST /logout -> logout()
 */
class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    /**
     * Show login page
     *
     * @return View
     */
    public function index(): View
    {
        return view('pages.login');
    }

    /**
     * Perform a login attempt
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS))
        {
            event(new Lockout($request));
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.'
            ], 429);
        }

        $remember = $request->boolean('remember');
        if (Auth::attempt($request->only('email', 'password'), $remember))
        {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            return response()->json([
                'message'     => 'Login successful!',
                'redirect_to' => route('home'),
            ]);
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Perform a logout attempt
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
