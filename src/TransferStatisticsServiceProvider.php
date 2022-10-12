<?php

namespace Xtheme\TransferStatistics;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Xtheme\TransferStatistics\TransferStatisticsMiddleware;

class TransferStatisticsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('transfer-statistics', TransferStatisticsMiddleware::class);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/transfer-statistics.php', 'transfer-statistics');
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
            __DIR__ . '/../config/transfer-statistics.php' => config_path('transfer-statistics.php'),
        ], 'config');
    }
}
