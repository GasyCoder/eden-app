<?php

namespace App\Providers;

use App\Support\SeoSettings;
use App\Support\PagePerformanceSettings;
use Illuminate\Support\Facades\View;
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
        // Injecte les réglages de référencement (titre, description, mots-clés,
        // image de partage, favicon) et les assets critiques dans le gabarit racine.
        View::composer('app', function ($view): void {
            $view->with([
                'seo' => SeoSettings::all(),
                'performance' => PagePerformanceSettings::all(),
            ]);
        });
    }
}
