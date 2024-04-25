<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $table = "users_settings";
    public $fillable = ["key","value","user_id"];
    protected $appends = ["full_link"];

    public function getFullLinkAttribute() {
        $link = $this->value;
        $name = $this->name;
        if ($name === "telegram") {
            return "https://t.me/".$link;
        }
        return $link;
    }
}
