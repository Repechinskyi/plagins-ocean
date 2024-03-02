<?php

namespace Asmet\Ckeditor;

use Illuminate\Support\ServiceProvider;


class CkeditorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/' => config_path()], 'config');
        $this->publishes([__DIR__ . '/../resources/js/' => resource_path('vendor/ckeditor')], 'resources');
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
