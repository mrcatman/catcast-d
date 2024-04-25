<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RadioPlaybackHistoryItem extends Model
{
    public $table = 'radio_playback_history';

    public function track() {
        return $this->hasOne('App\Models\Audio','id','radio_file_id');
    }

    public function playlist() {
        return $this->hasOne('App\Models\RadioPlaylist','id','playlist_id');
    }
}
