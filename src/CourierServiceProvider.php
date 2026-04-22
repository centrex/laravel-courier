<?php

declare(strict_types = 1);

namespace Centrex\Courier;

use Centrex\Courier\Services\{PathaoService, RedxService, RokomariService, SteadfastService, SundarbanService};
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

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
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'courier');

        $this->app->singleton(PathaoService::class, function ($app): PathaoService {
            return new PathaoService(
                $app->make(HttpFactory::class),
                $app['config']->get('courier', []),
            );
        });

        $this->app->singleton(RokomariService::class, function ($app): RokomariService {
            return new RokomariService(
                $app->make(HttpFactory::class),
                $app['config']->get('courier', []),
            );
        });

        $this->app->singleton(RedxService::class, function ($app): RedxService {
            return new RedxService(
                $app->make(HttpFactory::class),
                $app['config']->get('courier', []),
            );
        });

        $this->app->singleton(SteadfastService::class, function ($app): SteadfastService {
            return new SteadfastService(
                $app->make(HttpFactory::class),
                $app['config']->get('courier', []),
            );
        });

        $this->app->singleton(SundarbanService::class, function ($app): SundarbanService {
            return new SundarbanService(
                $app->make(HttpFactory::class),
                $app['config']->get('courier', []),
            );
        });

        $this->app->singleton(Courier::class, function ($app): Courier {
            return new Courier(
                $app->make(PathaoService::class),
                $app->make(RokomariService::class),
                $app->make(RedxService::class),
                $app->make(SteadfastService::class),
                $app->make(SundarbanService::class),
            );
        });

        $this->app->alias(Courier::class, 'courier');
    }
}
