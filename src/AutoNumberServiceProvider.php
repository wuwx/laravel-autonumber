<?php

namespace Wuwx\LaravelAutoNumber;

use Illuminate\Support\ServiceProvider;
use Wuwx\LaravelAutoNumber\Observers\AutoNumberObserver;

class AutoNumberServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../config/autonumber.php' => config_path('autonumber.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AutoNumberObserver::class, function ($app) {
            return new AutoNumberObserver(new AutoNumber());
        });
    }
}
