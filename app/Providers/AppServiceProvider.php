<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // 👈 Tambahan buat HTTPS

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
        // Ganti jadi Tailwind biar rapi!
        Paginator::useTailwind(); 

        // 👈 Tambahan buat maksa jalan di HTTPS pas di Railway
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}