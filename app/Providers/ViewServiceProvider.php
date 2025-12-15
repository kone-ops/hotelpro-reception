<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\UiSetting;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Partager les paramètres UI avec toutes les vues
        View::composer('*', function ($view) {
            try {
                $cssVariables = UiSetting::getCssVariables();
                $view->with('uiCssVariables', $cssVariables);
            } catch (\Exception $e) {
                // En cas d'erreur (table pas encore créée), utiliser une chaîne vide
                $view->with('uiCssVariables', '');
            }
        });
    }
}
