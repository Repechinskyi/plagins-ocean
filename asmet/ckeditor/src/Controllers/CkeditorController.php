<?php

namespace Asmet\Ckeditor\Controllers;


use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Routing\Controller;

class CkeditorController extends Controller
{
    public function store(Request $request)
    {
        $src = $request['src'];
        $name = $request['alt'];
        $style = config('ckeditor.style');

        if ($src && $name) {
            $image = change_image_to_editor($src, 2, $name);

            if ($image) {
                return response([
                    'src' => $image->getStyleUrl($style),
                ]);
            }
        } else {
            if ($file = $request['file'][0]) {
                $name = $request['alt'] ?? 'ckeditor-img';
                $now = Carbon::now();
//                $id = $now->day . $now->month . $now->year;
                $id = $now->timestamp;

                $fileUploaded = save_file($file, $name, $id, 'ckeditor', 'ckeditor');


                return response([
                    'src' => $fileUploaded->getStyleUrl($style),
                ]);
            }
        }
    }


    public function delete(Request $request)
    {
        if($src = $request['src']) {
            change_image_to_editor($src);
        }
    }
}
