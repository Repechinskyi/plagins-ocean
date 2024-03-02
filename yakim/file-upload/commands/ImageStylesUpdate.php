<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Yakim\FileUpload\FileUpload;
use Yakim\FileUpload\Jobs\UpdateImageStyles;

class ImageStylesUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fileupload:styles_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates image styles created by fileUpload package';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        FileUpload::chunk(50, function ($files) {
            $job = new UpdateImageStyles($files);
            dispatch($job);
        });
    }
}
