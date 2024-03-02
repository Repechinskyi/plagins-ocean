<?php

namespace Yakim\FileUpload;

use Illuminate\Database\Eloquent\Model;

/**
 * Yakim\FileUpload\ImageStyle
 *
 * @property int $id
 * @property int $file_upload_id
 * @property string $style
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Yakim\FileUpload\FileUpload $file
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload\ImageStyle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload\ImageStyle whereFileUploadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload\ImageStyle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload\ImageStyle whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yakim\FileUpload\ImageStyle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ImageStyle extends Model
{
    protected $fillable = ['id', 'file_upload_id', 'style', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(FileUpload::class);
    }
}
