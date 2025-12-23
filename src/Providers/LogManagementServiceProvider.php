<?php

namespace UtkarshGayguwal\LogManagement\Providers;

use UtkarshGayguwal\LogManagement\Services\LoggerService;
use UtkarshGayguwal\LogManagement\Http\Controllers\LogController;
use UtkarshGayguwal\LogManagement\Filters\LogFilter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class LogManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__.'/../Config/log-management.php', 
            'log-management'
        );

        // Register LoggerService as singleton
        $this->app->singleton(LoggerService::class, function ($app) {
            return new LoggerService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Config/log-management.php' => config_path('log-management.php'),
            ], 'log-management-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../Database/Migrations/' => database_path('migrations'),
            ], 'log-management-migrations');

            // Publish seeders
            $this->publishes([
                __DIR__.'/../Database/Seeders/' => database_path('seeders'),
            ], 'log-management-seeders');
        }

        // Load routes
        if (config('log-management.auto_discover.routes', true)) {
            $this->loadRoutes();
        }
    }

    /**
     * Load package routes.
     */
    protected function loadRoutes(): void
    {
        Route::prefix(config('log-management.api.prefix', 'api/logs'))
            ->middleware(config('log-management.api.middleware', ['api']))
            ->group(function () {
                Route::get('/', [LogController::class, 'index']);
                Route::get('/{id}', [LogController::class, 'show']);
            });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            LoggerService::class,
        ];
    }
}