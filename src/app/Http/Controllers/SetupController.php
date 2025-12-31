<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;

/**
 * Handles initial application setup. This controller is designed to be executed exactly
 * once, during the first initialization of the application.
 *
 * Routes:
 *  - GET /setup  -> show()
 *  - POST /setup -> initialize()
 *
 * Setup completion state is determined via `config('settings.setup_completed')`,
 * which is loaded from the database by the AppServiceProvider.
 */
class SetupController extends Controller
{
    /**
     * Display the application setup page.
     *
     * @return RedirectResponse|View
     */
    public function show(): mixed
    {
        if ($this->isInitializationCompleted())
        {
            return redirect('/');
        }

        return view('pages.setup');
    }

    /**
     * Creates the initial administrator user and permanently marks the
     * application as initialized. This action can only be executed once.
     *
     * @param  Request  $request
     *
     * @return RedirectResponse
     */
    public function initialize(Request $request): RedirectResponse
    {
        if ($this->isInitializationCompleted())
        {
            return redirect('/');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($validated)
        {
            if (!User::exists())
            {
                User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => UserRole::ADMIN->value,
                ]);
            }

            Setting::updateOrCreate(
                ['key' => 'setup_completed'],
                ['value' => 1],
            );
        });

        return redirect('/');
    }

    /**
     * Check if application setup has already been completed.
     *
     * @return bool
     */
    protected function isInitializationCompleted(): bool
    {
        return config('settings.setup_completed', 0);
    }
}
