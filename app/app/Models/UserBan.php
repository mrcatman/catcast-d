<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class UserBan extends Model {

    public $table = 'users_bans';
    public $dates = ['banned_till'];
    public $guarded = [];
    public $with = ['user:id,username', 'banned_by_user:id,username'];

    const USER_ID_KEY = 'banned_by';

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function banned_by_user() {
        return $this->belongsTo(User::class, 'banned_by', 'id');
    }


}
