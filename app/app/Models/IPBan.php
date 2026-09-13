<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class IPBan extends Model {

    protected $table = 'ip_bans';
    public $guarded = [];
    public $with = ['banned_by_user:id,username'];

    const USER_ID_KEY = 'banned_by';

    public function banned_by_user() {
        return $this->belongsTo(User::class, 'banned_by', 'id');
    }

}
