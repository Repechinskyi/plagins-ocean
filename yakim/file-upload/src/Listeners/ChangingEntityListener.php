<?php

namespace Yakim\FileUpload\Listeners;

use Yakim\FileUpload\Events\ChangingEntityEvent;

class ChangingEntityListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ChangingEntityEvent $event
     * @return void
     */
    public function handle(ChangingEntityEvent $event)
    {
        $entity = $event->file_upload->fileable_type;
        if(class_exists($entity)) {
            $entity = $entity::find($event->file_upload->fileable_id);

            if (!empty($entity)) {
                $entity->touch();
            }
        }
    }
}
