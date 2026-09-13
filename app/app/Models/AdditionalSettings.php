<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalSettings extends Model {
    protected $table = "additional_settings";
    protected $guarded = [];
    protected $casts = [
        'values' => 'array'
    ];

}
