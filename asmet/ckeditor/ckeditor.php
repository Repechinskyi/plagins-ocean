<?php

use Yakim\FileUpload\FileUpload;


function editor_save($new_content, $old_content = '')
{
    $new_content = str_replace('&nbsp;', ' ', $new_content);
    $new_content = preg_replace('~cellspacing="[^"]*"~i', '', $new_content);
    $new_content = preg_replace('~border="[^"]*"~i', '', $new_content);
    $new_content = preg_replace('~cellpadding="[^"]*"~i', '', $new_content);
    $new_content = preg_replace('~hspace="[^"]*"~i', '', $new_content);

    if ($old_content) {
        $old_article_images = image_for_editor($old_content);
        $new_article_images = image_for_editor($new_content);
        $result_images = array_diff($old_article_images, $new_article_images);
        foreach ($result_images as $result_image) {
            change_image_to_editor($result_image);
        }
    }

    return $new_content;
}

// $chose = 1 - delete file
// $chose = 2 - rename file
function change_image_to_editor(string $src, $chose = 1, string $name = 'ckeditor-img') {
    $src = parse_url($src, PHP_URL_PATH);
    $style = config('ckeditor.style');
    $url = str_replace('/'.$style, '', $src);
    $image = FileUpload::where('url', $url)->first();
    if($image) {
        $chose = (int) $chose;

        if($chose === 1) {
            delete_file($image->id);
        } elseif($chose === 2 && $name) {
            rename_file($image->id, $name);
            $image = FileUpload::find($image->id);
            return $image;
        }
    }

}

function editor_delete($string)
{
    $array_src = image_for_editor($string);

        foreach ($array_src as $src) {
            change_image_to_editor($src);
        }
}

//
function image_for_editor($string) {
    $img_pattern = '/<((img|image)[\s]+[^>]';
    $img_src_pattern = '*src=[\'"]?)([^\'"\s>]+)([\'"]?[^>]';
    $img_end_pattern = '*>)/';

    preg_match_all("$img_pattern$img_src_pattern$img_end_pattern", $string, $array);
    $array_src = $array[3] ?? [];

  // delete https or http from src, need for correct array_dif
  $clear_array_src = [];
  foreach ($array_src as $src) {
    $clear_array_src[] = preg_replace("(^https?:)", "", $src);
  }

  return $clear_array_src;
}