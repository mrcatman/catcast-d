<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadioConnection extends Model
{
    protected $table = 'radio_connections';
    protected $fillable = ['channel_id','radio_id','radio_server','radio_data'];

    public function setRadioDataAttribute($value)
    {
        $this->attributes['radio_data'] = json_encode($value,JSON_UNESCAPED_UNICODE);
    }


    public function getRadioDataAttribute() {
        return json_decode($this->attributes['radio_data']);
    }
}
