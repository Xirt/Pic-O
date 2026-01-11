<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

use App\Models\Setting;
use App\Policies\AdminPolicy;

/**
 * Application service provider responsible for registering and bootstrapping
 * application services.
 *
 * Handles global configurations, enforces HTTPS when behind proxies,
 * registers authorization policies, and loads application settings from
 * the database.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Request::header('X-Forwarded-Proto') === 'https')
        {
           URL::forceScheme('https');
        }

        Gate::policy(AdminController::class, AdminPolicy::class);

        if (Schema::hasTable('settings'))
        {
            $settings = Setting::all()->pluck('value', 'key')->toArray();
            config(['settings' => $settings]);
        }

        if (($demoMode = env('demo_environment')) !== null)
        {
            config(['settings.demo_environment' => (int) $demoMode]);
        }
    }
}
