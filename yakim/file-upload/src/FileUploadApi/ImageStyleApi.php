<?php

namespace Yakim\FileUpload\FileUploadApi;

use Yakim\FileUpload\FileUpload;
use Yakim\FileUpload\ImageStyle;
use Approached\LaravelImageOptimizer\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use Image;

//to make it work setup httpd.conf file (open_basedir value to null)

class ImageStyleApi {

  /**
   * disk where images will be stored
   */
  protected $disk;

  /**
   * foder of a parent image
   */
  protected $image_folder;

  /**
   * name of a parent image
   */
  protected $image_name;

  /**
   * id of a parent image
   */
  protected $image_id;

  /**
   * extension of a parent image
   */
  protected $extension;

  /**
   * information for specific image params
   */
  protected $image_params;

  /**
   * information for original image optimization
   */
  protected $origin;

  /**
   * Construct file upload Object
   */
  public function __construct($image_info) {
    $this->disk = $image_info->disk;
    $this->image_folder = $image_info->folder;
    $this->image_name = $image_info->name;
    $this->image_id = $image_info->id;
    $this->extension = $image_info->type;
    $this->attachSettings();
  }

  /**
   * generate styles of image
   * generate styles for existing images
   */
  public function stylesGenerate() {
    if ($this->isImage()) {

      //optimize original image if its not a gif/svg
      if ($this->extension != 'gif' && $this->extension != 'svg' && $this->extension != 'webp') {
        $this->stylyze();
        $this->sizeReduce();
      }

      //create styles
      foreach ($this->image_params as $style => $style_params) {
        $this->putStyleToDisk($style);
        $this->attachStyle($style);

        if ($this->extension != 'gif' && $this->extension != 'svg' && $this->extension != 'webp') {
          $this->stylyze($style);
          $this->sizeReduce($style);
        }
      }
    }
  }

  /**
   * clear styles of image
   */
  public static function deleteStyles($fyle_id) {
    //we should delete file from disk before we delete file from DB
    $fyle = FileUpload::find($fyle_id);
    if ($fyle) {
      foreach ($fyle->styles as $style) {
        Storage::disk($fyle->disk)
          ->delete($fyle->folder . '/' . $style->style . '/' . $fyle->name);
        $style->delete();
      }
    }
  }

  /**
   * Check that we working with image
   */
  private function isImage() {
    $check = FALSE;

    $extension = $this->extension;

    if ($extension == 'gif' || $extension == 'png' || $extension == 'jpeg' || $extension == 'jpg' || $extension == 'svg' || $extension == 'webp') {
      $check = TRUE;
    }

    if (!Storage::disk($this->disk)
      ->exists($this->image_folder . '/' . $this->image_name)) {
      $check = FALSE;
    }

    return $check;
  }

  /**
   * Attach settings to work with
   */
  private function attachSettings() {
    $entity_type = explode('/', $this->image_folder);
    $entity_type = $entity_type[0];

    $full_settings = config('image');
    $settings = [];

    if (isset($full_settings['entities_image_styles'][$entity_type])) {
      $settings = $full_settings['entities_image_styles'][$entity_type];
    }

    $this->image_params = $settings;

    $this->origin = $full_settings['original_style'];
  }


  /**
   * get folder of style
   */
  private function getStyleFolder($style) {
    return $this->image_folder . '/' . $style;
  }

  /**
   * put style to disk
   */
  private function putStyleToDisk($style) {
    if (Storage::disk($this->disk)
      ->exists($this->getStyleFolder($style) . '/' . $this->image_name)) {
      Storage::disk($this->disk)
        ->delete($this->getStyleFolder($style) . '/' . $this->image_name);
    }
    Storage::disk($this->disk)
      ->copy($this->image_folder . '/' . $this->image_name,
        $this->getStyleFolder($style) . '/' . $this->image_name);
  }

  /**
   * attach style of image to original image
   */
  private function attachStyle($style) {
    $attach = ImageStyle::create([
      'file_upload_id' => $this->image_id,
      'style' => $style,
    ]);
  }

  /**
   * stylyze file
   */
  private function stylyze($style = FALSE) {
    $url = $this->getStyleUrl($style);
    if ($style) {
      $style = $this->image_params[$style];
    }
    else {
      $style = $this->origin;
    }

    $img = Image::make($url);

    $img->orientate();

    $this->resizeImage($img, $style);

    $this->addWatermark($img, $style);

    //reduce quality
    if (isset($style['quality'])) {
      $img->save($url, $style['quality']);
    }
    else {
      $img->save($url);
    }
  }

  /**
   * add watermark to image
   */
  private function addWatermark(&$img, $style) {

    if (isset($style['watermark'])) {
      //получим из конфига файл ватермарка
      $watermark = $this->getWatermark($style['watermark']);
      //Если задана в конфиге ширина картинки зададим ее
      if (isset($style['watermark_width'])) {
        $watermark->resize($style['watermark_width'], NULL, function ($constraint) {
          $constraint->aspectRatio();
        });
      }
      //Если указана повторение
      if (isset($style['watermark_repeat'])) {
        //начальное положение ватермарка по оси x
        $x = 0;
        //задаем цикл пока положение по оси x не выйдет за размер картинки
        while ($x < $img->width()) {
          //начальное положение ватермарка по оси y
          $y = 0;
          //задаем цикл пока положение по оси y не выйдет за размер картинки
          while ($y < $img->height()) {
            //вставим картинку по оси х и у
            $img->insert($watermark, 'top-left', $x, $y);
            //увеличим начальное положение по оси у на высоту ватермарка
            $y += $watermark->height();
          }
          //увеличим начальное положение по оси х на ширину ватермарка
          $x += $watermark->width();
        }
      }
      else {
        // insert watermark at bottom-right corner with 5px offset
        $position = 'bottom-right';

        if (isset($style['watermark_position'])) {
          $position = $style['watermark_position'];
        }

        $img->insert($watermark, $position, 5, 5);
      }


    }
  }

  /*
   * get watermark from disk
   */
  private function getWatermark($watermark) {
    $url = storage_path('app/public/' . $watermark);

    if (!file_exists($url)) {
      throw new \Exception("Watermark image was not found in: " . $url);
    }

    // create a new Image instance for inserting
    return Image::make($url);
  }

  /**
   * reduce size of image
   */
  private function resizeImage(&$img, $style) {
    if (isset($style['stretch']) && $style['stretch'] == TRUE) {
      if (isset($style['width']) && isset($style['height'])) {
        $img->fit($style['width'], $style['height'], function ($constraint) {
          $constraint->upsize();
        });
      }
    }
    else {
      if (isset($style['width'])) {
        $img->resize($style['width'], NULL, function ($constraint) {
          $constraint->aspectRatio();
          $constraint->upsize();
        });
      }
      if (isset($style['height'])) {
        $img->resize(NULL, $style['height'], function ($constraint) {
          $constraint->aspectRatio();
          $constraint->upsize();
        });
      }
    }
  }

  /**
   * reduce size of image
   */
  private function sizeReduce($style = FALSE) {
    $imageOptimizer = new ImageOptimizer;
    $imageOptimizer->optimizeImage($this->getStyleUrl($style));
  }

  /**
   * reduce size of image
   */
  private function getStyleUrl($style = FALSE) {
    if ($style) {
      $url = storage_path('app/public/' . $this->getStyleFolder($style) . '/' . $this->image_name);
    }
    else {
      //for original image
      $url = storage_path('app/public/' . $this->image_folder . '/' . $this->image_name);
    }
    return $url;
  }

}

