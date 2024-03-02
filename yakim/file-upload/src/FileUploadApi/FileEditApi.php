<?php

namespace Yakim\FileUpload\FileUploadApi;

use Illuminate\Support\Facades\Storage;
use Behat\Transliterator\Transliterator;
use Yakim\FileUpload\FileUpload;

class FileEditApi
{
    /**
     * delete file from DB and disk
     */
    public static function delete($file_id)
    {
        //we should delete file from disk before we delete file from DB
        $file = FileUpload::find($file_id);
        if($file) {
            ImageStyleApi::deleteStyles($file_id);
            Storage::disk($file->disk)->delete($file->folder . '/' . $file->name);
            $files = Storage::disk($file->disk)->files($file->folder);
            if (empty($files)) {
                Storage::disk($file->disk)->deleteDirectory($file->folder);
            }
            $file->delete();
        }
    }

    /**
     * rename file
     */
    public static function rename($file_id, $new_name)
    {
        $file = FileUpload::find($file_id);
        if($file) {
            $description = $new_name;

            //create new file name
            $new_name = Transliterator::transliterate($new_name, '-');
            $new_name_full = $new_name . '.' . $file->type;

            if ($new_name_full != $file->name) {

                //check to not overwrite file
                $new_name_full = FileEditApi::noOverWrite($file->disk, $file->folder, $new_name_full);

                //delete file styles before file move
                $file_has_styles = false;
                if (!empty($file->styles)) {
                    ImageStyleApi::deleteStyles($file_id);
                    $file_has_styles = true;
                }

                //rename file on the disk
                Storage::disk($file->disk)->move($file->folder . '/' . $file->name,
                    $file->folder . '/' . $new_name_full);

                //rename file in DB
                $url = Storage::disk($file->disk)->url($file->folder . '/' . $new_name_full);
                $url = parse_url($url, PHP_URL_PATH);
                $file->update([
                    'name' => $new_name_full,
                    'description' => $description,
                    'url' => $url,
                ]);

                //generate file styles for moved files
                if ($file_has_styles) {
                    $preview_styles = new ImageStyleApi($file);
                    $preview_styles->stylesGenerate();
                }
            }
        }
    }

    /**
     * generates new file name if file with sutch name already exist in the folder
     */
    public static function noOverWrite($disk, $folder, $file_name_full)
    {
        //get file extention from file name
        $divided_name = explode(".", $file_name_full);
        $ext = end($divided_name);

        //get file name without extention
        $divided_name = array_flip($divided_name);
        unset ($divided_name[$ext]) ;
        $divided_name = array_flip($divided_name);
        $file_name = implode('.', $divided_name);

        //generate name that wont owerwrite file
        $i = 0;
        while (Storage::disk($disk)->exists($folder . '/' . $file_name_full)) {
            $i++;
            $file_name_full = $file_name . '_' . $i . '.' . $ext;
        }
        return $file_name_full;
    }



    /**
     * change entity properties of a file and move file to the new directory
     */
//    public static function changeEntity($file_id, $new_entity_type, $new_entity_id)
//    {
//        $file = FileUpload::find($file_id);
//        $folder = $new_entity_type . '/' . $new_entity_id;
//
//        //check to not overwrite file
//        $new_name_full = FileEditApi::noOverWrite($file->disk, $folder, $file->name);
//
//        //delete file styles before file move
//        $file_has_styles = false;
//        if(!empty($file->styles)) {
//            ImageStyleApi::deleteStyles($file_id);
//            $file_has_styles = true;
//        }
//
//        //move file on the disk
//        Storage::disk($file->disk)->move($file->folder . '/' . $file->name, $folder . '/' . $new_name_full);
//
//        //update file data in DB
//        $url = Storage::disk($file->disk)->url($folder . '/' . $new_name_full);
//        $url = parse_url($url, PHP_URL_PATH);
//        $file->update([
//            'name' => $new_name_full,
//            'url' => $url,
//            'folder' => $folder,
//            'fileable_id' => $new_entity_id,
//        ]);
//
//        //generate file styles for moved files
//        if($file_has_styles) {
//            $preview_styles = new ImageStyleApi($file);
//            $preview_styles->stylesGenerate();
//        }
//    }

}
