<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {

    protected $table = 'attachments';
    protected $appends = ['data'];

    public const TYPE_PICTURE = 'picture';
    public const TYPE_VIDEO = 'video';

    public function getDataAttribute() {
        return $this->relation;
    }

    public function relation() {
        if ($this->attachment_type == self::TYPE_PICTURE) {
           return $this->belongsTo(Picture::class,'attachment_id','id');
        } else if ($this->attachment_type == self::TYPE_VIDEO) {
            return $this->belongsTo(Media::class, 'attachment_id','id');
        }
    }
}
