<?php

namespace Yakim\FileUpload\Jobs;

use Yakim\FileUpload\FileUploadApi\ImageStyleApi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateImageStyles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $files;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->files as $file) {

            if(!empty($file->styles)) {
                ImageStyleApi::deleteStyles($file->id);
            }

            $preview_styles = new ImageStyleApi($file);
            $preview_styles->stylesGenerate();
        }
    }
}

