<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StreamKey extends Model
{
    public $guarded = [];
    public $appends = ['full_key'];

    public function generateKey($user_id,$channel_id) {
        return $user_id."_".$channel_id."_".Str::random(16);
    }

    public function getFullKeyAttribute() {
        return $this->channel_id. '?key='.$this->key;
    }

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
