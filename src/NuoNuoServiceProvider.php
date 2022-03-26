<?php

namespace Fatryst\NuoNuo;

use Illuminate\Support\ServiceProvider;

class NuoNuoServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'fatryst');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'fatryst');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nuonuo.php', 'nuonuo');

        // Register the service the package provides.
        $this->app->singleton('nuonuo', function ($app) {
            return new NuoNuo($app['session'], $app['config']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['nuonuo'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/nuonuo.php' => config_path('nuonuo.php'),
        ], 'nuonuo.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/fatryst'),
        ], 'nuonuo.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/fatryst'),
        ], 'nuonuo.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/fatryst'),
        ], 'nuonuo.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
