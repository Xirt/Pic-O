<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

use App\Models\Setting;
use App\Policies\AdminPolicy;

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
        Gate::policy(AdminController::class, AdminPolicy::class);

        if (Schema::hasTable('settings'))
        {
            $settings = Setting::all()->pluck('value', 'key')->toArray();
            config(['settings' => $settings]);
        }

    }
}
