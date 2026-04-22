<?php

declare(strict_types = 1);

namespace Centrex\Courier;

use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'courier');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('courier.php'),
            ], 'courier-config');

            // Publishing the migrations.
            /*$this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations')
            ], 'courier-migrations');*/

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/courier'),
            ], 'courier-views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/courier'),
            ], 'courier-assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/courier'),
            ], 'courier-lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'courier');

        // Register the main class to use with the facade
        $this->app->singleton('courier', function () {
            return new Courier();
        });
    }
}
