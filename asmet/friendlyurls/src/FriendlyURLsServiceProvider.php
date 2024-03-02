<?php

namespace Asmet\FriendlyURLs;

use Illuminate\Support\ServiceProvider;

class FriendlyURLsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/' => config_path() . "/"], 'config');
        $this->publishes([__DIR__ . '/../database/' => base_path("database")], 'database');

        $this->loadViewsFrom(__DIR__ . '/../views', 'alias');
    }

    public function register()
    {

    }
}
