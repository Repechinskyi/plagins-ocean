<?php

namespace Yakim\FileUpload\Listeners;

use Yakim\FileUpload\Events\deletingEntityEvent;

class deletingEntityListener
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
     * @param  deletingEntityEvent $event
     * @return void
     */
    public function handle(deletingEntityEvent $event)
    {
        $entity = $event->file_upload->fileable_type;
        if(class_exists($entity)) {
            $entity = $entity::find($event->file_upload->fileable_id);

            if (!empty($entity)) {
                $entity->touch();
            }
        }

        if($styles = $event->file_upload->styles) {
            foreach($styles as $style) {
                $style->delete();
            }
        }
    }
}
