<?php

namespace Yakim\FileUpload;

use Illuminate\Database\Eloquent\Model;
use Yakim\FileUpload\Events\ChangingEntityEvent;
use Yakim\FileUpload\Events\deletingEntityEvent;
use Storage;

/**
 * Yakim\FileUpload
 *
 * @property int $id
 * @property string $name
 * @property int $weight
 * @property string $description
 * @property string $folder
 * @property string $disk
 * @property string $system_name
 * @property string $url
 * @property string $type
 * @property string|null $mime
 * @property string|null $mime_type
 * @property int $fileable_id
 * @property string $fileable_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $fileUploadAble
 * @property-read \Illuminate\Database\Eloquent\Collection|\Yakim\FileUpload\ImageStyle[]
 *   $styles
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereFileableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereFileableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload
 *   whereWeight($value)
 * @mixin \Eloquent
 */
class FileUpload extends Model {

  protected $fillable = [
    'id',
    'name',
    'weight',
    'url',
    'description',
    'folder',
    'disk',
    'system_name',
    'type',
    'mime',
    'mime_type',
    'fileable_id',
    'fileable_type',
    'created_at',
    'updated_at',
  ];

  protected $dispatchesEvents = [
    'saved' => ChangingEntityEvent::class,
    'deleting' => deletingEntityEvent::class,
  ];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\MorphTo
   */
  public function fileUploadAble() {
    return $this->morphTo();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function styles() {
    return $this->hasMany(ImageStyle::class);
  }

  /**
   * For legacy modules
   *
   * @param $style
   * @param $dumb
   *
   * @return string
   */
  public function getStylepath($style, $dumb = 'dumb') {
    return $this->getStyleUrl($style);
  }

  /**
   * @param $style
   *
   * @return string
   */
  public function getStyleUrl($style) {
    $is_exists = Storage::disk($this->disk)
      ->exists("{$this->folder}/{$style}/{$this->name}");

    $image = null;
    if ($is_exists) {
      $full_url = Storage::disk($this->disk)
        ->url("{$this->folder}/{$style}/{$this->name}");
      $full_url = explode('/storage', $full_url);
      if (isset($full_url[1])) {
        $image = '/storage' . $full_url[1] . '?t=' . strtotime($this->updated_at);
      }
    }

    return $image;
  }

  /**
   * Глобальная сортировка по весу
   *
   * Чтобы получить выборку без сортировки:
   * MyModel::withoutGlobalScope('order')->get();
   */
  protected static function boot() {
    parent::boot();
    static::addGlobalScope('order', function ($builder) {
      $builder->orderBy('weight', 'asc');
    });
  }
}
