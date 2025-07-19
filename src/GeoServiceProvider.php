<?php

namespace OpensourceLabsGh\GeoUtils;

use Illuminate\Support\ServiceProvider;
use OpensourceLabsGh\GeoUtils\Console\Commands\GeoUtilsCommand;

class GeoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('geo-helper', function () {
            return new GeoHelper();
        });

        $this->app->alias('geo-helper', GeoHelper::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        logger()->info('GeoUtils config loaded');
        $this->loadConfig();
        $this->loadCommands();
    }

    /**
     * Load package configuration.
     */
    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/../config/geo-utils.php';
        
        $this->publishes([
            $configPath => config_path('geo-utils.php'),
        ], 'geo-utils-config');

        $this->mergeConfigFrom($configPath, 'geo-utils');
    }

    /**
     * Load package commands.
     */
    private function loadCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GeoUtilsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['geo-helper', GeoHelper::class];
    }
}