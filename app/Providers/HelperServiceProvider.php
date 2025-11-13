<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register any helper functions or services here.
     *
     * This provider ensures that all helper functions are loaded
     * properly in production environments including shared hosting.
     */
    public function register()
    {
        // Helper classes are autoloaded via composer.json
        // No manual registration required for static classes
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Any bootstrap logic for helpers can go here
    }
}