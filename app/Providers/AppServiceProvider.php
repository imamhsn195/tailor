<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        // Register theme-default views
        $themePath = base_path('../laravel-theme-manager/Themes/default/resources/views');
        
        // Fallback to vendor path if theme is installed via composer
        if (!file_exists($themePath)) {
            $themePath = base_path('vendor/imamhsn195/laravel-theme-manager-default/resources/views');
        }
        
        if (file_exists($themePath)) {
            View::addNamespace('theme-default', $themePath);
        }
    }
}
