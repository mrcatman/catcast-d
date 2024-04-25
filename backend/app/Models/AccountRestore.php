<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRestore extends Model {

    public $table = 'account_restores';

    protected $guarded = [];

    public function accountConnection() {
        return $this->belongsTo(AccountConnection::class);
    }
}
