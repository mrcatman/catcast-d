<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRegistrationRequest extends Model {

    public $table = 'user_registration_requests';
    public $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
