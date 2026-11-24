<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Forcer l'URL de base (APP_URL) pour que route(), url(), asset() génèrent les bons liens
        if ($baseUrl = config('app.url')) {
            URL::forceRootUrl($baseUrl);
        }
    }
}
