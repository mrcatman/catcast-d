<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model {

    public $table = 'logos';
    protected $with = ['picture'];
    protected $guarded = [];
    protected $casts = [
        'position' => 'array',
    ];

    public function picture() {
        return $this->belongsTo(Picture::class,"picture_id","id");
    }
}
