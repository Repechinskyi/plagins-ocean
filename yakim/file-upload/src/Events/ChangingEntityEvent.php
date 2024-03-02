<?php

namespace Yakim\FileUpload\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Yakim\FileUpload\FileUpload;

class ChangingEntityEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $file_upload;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(FileUpload $file_upload)
    {
        $this->file_upload = $file_upload;
    }
}
