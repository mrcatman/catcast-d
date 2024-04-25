<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistItem extends Model
{
    protected $table = 'blacklist';

    public $timestamps = false;
    protected $hidden = ['from_id', 'to_id'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'to_id', 'id');
    }
}

