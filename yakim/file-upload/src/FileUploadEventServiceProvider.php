<?php

namespace Yakim\FileUpload;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as LServiceProvider;

class FileUploadEventServiceProvider extends LServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Yakim\FileUpload\Events\ChangingEntityEvent' => [
            'Yakim\FileUpload\Listeners\ChangingEntityListener'
        ],
        'Yakim\FileUpload\Events\deletingEntityEvent' => [
            'Yakim\FileUpload\Listeners\deletingEntityListener'
        ],
    ];
}
