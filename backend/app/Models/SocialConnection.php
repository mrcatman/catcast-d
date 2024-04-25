<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialConnection extends Model
{
    public $fillable = ['provider_user_id','provider_name','user_id'];

    public function user() {
        return $this->hasOne('App\Models\User','id','user_id');
    }
}
