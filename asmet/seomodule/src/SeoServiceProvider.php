<?php

namespace Asmet\SeoModule;

use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/' => config_path() . "/"], 'config');
        $this->publishes([__DIR__ . '/../database/' => database_path()], 'database');

        $this->loadViewsFrom(__DIR__ . '/../views', 'seo');
    }

    public function register()
    {

    }
}