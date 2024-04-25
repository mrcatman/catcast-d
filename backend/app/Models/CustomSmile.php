<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomSmile extends Model
{
    public $table = 'custom_smileys';
    public $guarded = [];

    public function picture() {
        return $this->belongsTo(Picture::class);
    }
}
