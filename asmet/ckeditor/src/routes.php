<?php

Route::post('ckeditor-file-upload', '\Asmet\Ckeditor\Controllers\CkeditorController@store');
Route::post('ckeditor-file-delete', '\Asmet\Ckeditor\Controllers\CkeditorController@delete');
