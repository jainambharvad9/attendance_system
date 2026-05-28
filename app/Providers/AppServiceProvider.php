<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for older MySQL/MariaDB installations: limit default string length
        // to avoid "Specified key was too long" errors with utf8mb4.
        // Use 120 to be safe on servers with small max index byte limits.
        Schema::defaultStringLength(120);
    }
}
