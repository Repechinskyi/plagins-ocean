<?php

use Illuminate\Support\Facades\Storage;
use Yakim\FileUpload\FileUploadApi\FileEditApi;
use Yakim\FileUpload\FileUploadApi\FileUploadApi;

function save_file(
  $file,
  $file_name,
  $fileable_id,
  $fileable_namespace,
  $settings_entity_type = 'default',
  $disk = 'public',
  $system_name = null
) {
  $preview = new FileUploadApi($file, $fileable_id, $fileable_namespace,
    $settings_entity_type, $file_name, $disk, $system_name);
  return $preview->save();
}

function delete_file($file_id) {
  FileEditApi::delete($file_id);
}

function rename_file($file_id, $new_name) {
  FileEditApi::rename($file_id, $new_name);
}

function files_quantity($name = 'default') {
  return config("uploadform.quantity.{$name}");
}

// Deprecated
function saveFile(
  $file,
  $file_name,
  $fileable_id,
  $fileable_namespace,
  $settings_entity_type = 'default',
  $disk = 'public'
) {
  return save_file(
    $file,
    $file_name,
    $fileable_id,
    $fileable_namespace,
    $settings_entity_type,
    $disk
  );
}

function deleteFile($file_id) {
  delete_file($file_id);
}

function renameFile($file_id, $new_name) {
  rename_file($file_id, $new_name);
}