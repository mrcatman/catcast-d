<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaUploadKey extends Model {

    protected $table = 'media_upload_keys';

    protected $guarded = [];

    public function media() {
        return $this->belongsTo(Media::class);
    }

}
