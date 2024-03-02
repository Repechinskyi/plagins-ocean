<?php

namespace Yakim\FileUpload\FileUploadApi;

use Illuminate\Support\Facades\Storage;
use Behat\Transliterator\Transliterator;
use Yakim\FileUpload\FileUpload as FileUpload;

use Approached\LaravelImageOptimizer\ImageOptimizer;
use Image;
use Yakim\FileUpload\FileUploadApi\ImageStyleApi;

class FileUploadApi {

  /**
   * Entity namespace for attaching file to entities
   */
  protected $entity_namespace;

  /**
   * Entity type, which uploads files = folder name for files
   */
  protected $entity_type;

  /**
   * Entity id, which uploads files = sub folder fo files
   * INTEGER
   */
  protected $entity_id;

  /**
   * File object to work with
   */
  protected $file;

  /**
   * Desired name of uploaded file
   */
  protected $desired_name;

  /**
   * Disk where file should be placed
   */
  public $disk;

  /**
   * System name for delimitation of pictures of one material
   */
  public $system_name;

  /**
   * Construct file upload Object
   */
  public function __construct($file, $entity_id, $entity_namespace, $entity_type, $desired_name, $disk, $system_name) {
    $this->file = $file;
    $this->entity_id = $entity_id;
    $this->entity_namespace = $entity_namespace;
    $this->entity_type = $entity_type;
    $this->desired_name = $desired_name;
    $this->disk = $disk;
    $this->system_name = $system_name;
  }

  /**
   * save file to the disk and DB
   */
  public function save() {
    $name = $this->nameGenerate();
    $folder = $this->folderGenerate();

    //check to not overwrite file
    $i = 0;
    while (Storage::disk($this->disk)->exists($folder . '/' . $name)) {
      $i++;
      $name = $this->nameGenerate('_' . $i);
    }

    $this->putToDisk($folder, $name);

    //execute for existing files!!
    $file_info = $this->attachFile($folder, $name);

    //generate styles for image and optimize original image
    $preview_styles = new ImageStyleApi($file_info);
    $preview_styles->stylesGenerate();
    return $file_info;
  }

  /**
   * Create file name
   */
  private function nameGenerate($suffix = '') {
    $original_name = $this->file->getClientOriginalName();
    $extension = $this->getExtensionFromName($original_name);
    $new_name = Transliterator::transliterate($this->desired_name, '-');

    //create new name
    $full_new_name = $new_name . $suffix . '.' . $extension;

    return $full_new_name;
  }

  /**
   * Create file name
   */
  private function folderGenerate() {
    $namespase = Transliterator::transliterate($this->entity_namespace, '-');

    $folder = $this->entity_type . '/' . $namespase . '/' . $this->entity_id;

    return $folder;
  }

  /**
   * generate path for file
   */
  private function urlGenerate($folder, $name) {
    return Storage::disk($this->disk)->url($folder . '/' . $name);
  }

  /**
   * Get file extension form file name
   */
  private function getExtensionFromName($name) {
    $parts = explode(".", $name);
    $ext = end($parts);
    $ext = strtolower($ext);
    return $ext;
  }

  /**
   * Attach file to sppecific entity
   */
  private function attachFile($folder, $name) {
    $url = $this->urlGenerate($folder, $name);
    $url = parse_url($url, PHP_URL_PATH);
    $files_count = FileUpload::where([
      'fileable_type' => $this->entity_namespace,
      'fileable_id' => $this->entity_id,
    ])->count();
    $file_mime = $this->file->getClientMimeType();
    $file_mime = explode('/', $file_mime);
    $attach = FileUpload::create([
      'name' => $name,
      'weight' => $files_count,
      'url' => $url,
      'type' => $this->getExtensionFromName($name),
      'mime' => $file_mime[0] ?? null,
      'mime_type' => $file_mime[1] ?? null,
      'disk' => $this->disk,
      'folder' => $folder,
      'system_name' => $this->system_name,
      'description' => $this->desired_name,
      'fileable_id' => $this->entity_id,
      'fileable_type' => $this->entity_namespace,
    ]);

    return $attach;
  }

  /**
   * put file to our disk
   */
  private function putToDisk($folder, $name) {
    return Storage::disk($this->disk)
      ->putFileAs($folder, $this->file, $name, $this->disk);
  }
}
