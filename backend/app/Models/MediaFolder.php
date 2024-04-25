<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    protected $table = 'folders';

    protected $guarded = [];

    public function scopeSearch($filter, $search) {
        return $filter->where(function($query) use ($search) {
            $query->where('title', 'LIKE', '%'.$search.'%');
        });
    }

}
