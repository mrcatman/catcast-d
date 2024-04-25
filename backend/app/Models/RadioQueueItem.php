<?php

namespace App\Models;

use App\Models\Audio;
use Illuminate\Database\Eloquent\Model;

class RadioQueueItem extends Model
{
    public $table = 'radio_queue';
    public function track() {
        return $this->hasOne('App\Models\Audio','id','track_id');
    }
}
