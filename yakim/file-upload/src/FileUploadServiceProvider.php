<?php

namespace Yakim\FileUpload;


use Illuminate\Support\ServiceProvider as LServiceProvider;


class FileUploadServiceProvider extends LServiceProvider
{

    public function boot()
    {
        //Указываем что пакет должен опубликовать при установке
        $this->publishes([__DIR__ . '/../config/' => config_path() . "/"], 'config');
        $this->publishes([__DIR__ . '/../resources/' => resource_path("vendor/FileUpload/")], 'resources');
        $this->publishes([__DIR__ . '/../database/' => database_path()], 'migrations');
		$this->publishes([__DIR__ . '/../lang/' => resource_path("lang/")], 'lang');
		$this->publishes([__DIR__ . '/../resources/images/' => public_path("vendor/fileupload/images/")], 'public');
		$this->publishes([__DIR__ . '/../commands/' => base_path('app/Console/Commands/')]);
    }
}