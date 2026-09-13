<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoGridThumbnail extends Model {
    protected $table = 'video_grid_thumbnails';
    protected $guarded = [];

    protected $with = ['picture'];

    public function picture() {
        return $this->belongsTo(Picture::class);
    }

}
