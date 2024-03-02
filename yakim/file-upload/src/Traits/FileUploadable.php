<?php

namespace Yakim\FileUpload\Traits;

use Yakim\FileUpload\FileUpload;
use Yakim\FileUpload\FileUploadApi\FileUploadApi;

trait FileUploadable {

  private $file_pic = '/vendor/fileupload/images/file.svg';

  public function files() {
    return $this->morphMany(FileUpload::class, 'fileable');
  }

  /**
   * @param        $file
   * @param        $file_name
   * @param string $files_settings_entity_type
   * @param string $system_name
   * @param string $files_disk
   *
   * @return mixed
   */
  function saveFile($file, $file_name, $system_name, $files_settings_entity_type = 'default', $files_disk = 'public') {
    $preview = new FileUploadApi($file, $this->id, get_class($this), $files_settings_entity_type, $file_name, $files_disk, $system_name);
    return $preview->save();
  }

  /**
   * @param        $data
   * @param        $field_names
   * @param string $files_settings_entity_type
   * @param string $system_name
   * @param string $field_for_filename
   * @param string $files_disk
   *
   * @return array
   */
  public function saveFiles($data, $field_names, $files_settings_entity_type = 'default', $system_name = null, $field_for_filename = 'name', $files_disk = 'public') {
    if (is_string($field_names)) {
      $field_names = [$field_names];
    }
    $files_info = [];
    foreach ($field_names as $name) {
      $files = $data[$name] ?? null;
      $files_names = $data[$name . 'Names'] ?? null;
      $thumbs_order = $data[$name . 'ThumbsOrder'] ?? null;
      $thumbs_names = json_decode($data[$name . 'ThumbsNames']);
      if (!empty($files)) {
        foreach ($files as $key => $file) {
		  $file_name = !empty($files_names[$key]) ? $files_names[$key] . time() : $this->$field_for_filename . time();
          $files_info['info'][$name]['saved'][$key] = $this->saveFile($file, $file_name, $system_name, $files_settings_entity_type, $files_disk);
          unset($key);
          unset($file);
        }
      }
      if (!empty($thumbs_order)) {
        foreach ($thumbs_order as $weight => $id) {
          $file = $this->files()->find($id);
          if ($file) {
            if ($file->weight != $weight) {
              $file->update(['weight' => $weight]);
              $files_info['info'][$name]['weight_changed'][$id] = true;
            }
          }
          unset($weight);
          unset($id);
          unset($file);
        }
      }
      if (!empty($thumbs_names)) {
        foreach ($thumbs_names as $id => $f_name) {
          $file = $this->files()->find($id);
          if ($file) {
            if (!empty($f_name) && $file->description != $f_name) {
              renameFile($id, $f_name);
              $files_info['info'][$name]['name_changed'][$id] = true;
            }
          }
          unset($f_name);
          unset($id);
          unset($file);
        }
      }
    }
    return $files_info;
  }

  /**
   * @return array
   */
  public function getFilesThumbs($style = 'thumb_b') {
    $thumbs = [];
    foreach ($this->files as $key => $file) {
      $thumbs[$key]['thumb_path'] = $file->mime === 'image' ? $file->getStylepath($style) : $this->file_pic;
      $thumbs[$key]['alt'] = $file->description;
      $thumbs[$key]['id'] = $file->id;
    }
    return $thumbs;
  }

  /**
   * @param int $max
   *
   * @return int
   */
  public function maxFiles(int $max) {
    $num = $max - $this->files()->count();
    return $num < 0 ? 0 : $num;
  }

}