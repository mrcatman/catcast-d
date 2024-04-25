<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Audio;
class RadioServer extends Model
{
    public $table = 'radio_servers';

    public function getIpAddressAttribute() {
        if ($this->id == 2) {
            return "radio.fenixplustv.xyz";
        }
        if ($this->attributes['ip_address'] == "v1.catcast.me") {
            return "v1.catcast.tv";
        }
        return $this->attributes['ip_address'];
    }
}
