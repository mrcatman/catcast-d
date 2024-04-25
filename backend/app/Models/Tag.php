<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = ['entity_id','entity_type','tag'];

}
