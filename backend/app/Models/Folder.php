<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $table = 'folders';
    public $fillable = ["folder_title","folder_description","parent_id","playlist_id"];


    public function tracks()
    {
        return $this->hasMany('App\Models\Audio');
    }

    public function playlist() {
        return $this->belongsTo('App\Models\Playlist','connected_playlist_id','playlistid');
    }
}
