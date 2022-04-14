<?php

namespace Krognome\Approvalstudio;

use Illuminate\Support\ServiceProvider;

class ApprovalstudioServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'krognome');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'krognome');
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
        $this->mergeConfigFrom(__DIR__.'/../config/approvalstudio.php', 'approvalstudio');

        // Register the service the package provides.
        $this->app->singleton('approvalstudio', function ($app) {
            return new Approvalstudio;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['approvalstudio'];
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
            __DIR__.'/../config/approvalstudio.php' => config_path('approvalstudio.php'),
        ], 'approvalstudio.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/krognome'),
        ], 'approvalstudio.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/krognome'),
        ], 'approvalstudio.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/krognome'),
        ], 'approvalstudio.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
