<?php

namespace Asmet\Breadcrumbs;

use Illuminate\Support\ServiceProvider;

class BreadcrumbsServiceProvider extends ServiceProvider {
    
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/' => config_path() . "/"], 'config');
        
        $this->loadViewsFrom(__DIR__.'/../views', 'breadcrumbs');

    }

    public function register()
    {
        
    }

}
