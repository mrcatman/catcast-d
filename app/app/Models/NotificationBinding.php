<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationBinding extends Model {

    protected $table = "notification_bindings";
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
