<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSubscription extends Model {

    protected $table = "notification_subscriptions";
    public $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
