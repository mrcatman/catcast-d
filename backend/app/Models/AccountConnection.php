<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccountConnection extends Model {
    protected $table = "account_connections";
    public $fillable = ['account_type','account_name','confirm_status', 'user_id', 'confirm_code'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getConfirmationCanBeResentAttribute() {
        return (time() - $this->updated_at->timestamp > 60 * 5);
    }

}
