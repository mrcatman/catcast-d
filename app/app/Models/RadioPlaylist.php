<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class RadioPlaylist extends Model
{
    public $table = 'radio_playlists';
    public $fillable = ["channel_id","title","description","playback_weight","playback_type","playback_order","playback_data","cover","is_visible","can_accept_requests"];

    public $appends = ['playback_type_name'];

    public $types = [
        '0'=>'TYPE_NONE',
        '1'=>'TYPE_AROUND_THE_CLOCK',
        '2'=>'TYPE_SPECIFIED_TIME',
        '3'=>'TYPE_EVERY_N_MINUTES',
        '4'=>'TYPE_EVERY_N_TRACKS',
        '5'=>'TYPE_DAILY',
        '6'=>'TYPE_SPECIFIED_DATE',
    ];


    public function setPlaybackDataAttribute($value)
    {
        $this->attributes['playback_data'] = json_encode($value,JSON_UNESCAPED_UNICODE);
    }

    public function getPlaybackTypeNameAttribute() {
        return $this->types[$this->playback_type];
    }

    public function getPlaybackDataAttribute() {
        return json_decode($this->attributes['playback_data']);
    }

    public function tracks()
    {
        return $this->belongsToMany('App\Models\Audio','radio_files_playlists','playlist_id','radio_file_id');
    }

    public function folders()
    {
        return $this->belongsToMany('App\Models\Folder','radio_folders_playlists','playlist_id','radio_folder_id');
    }

    public function getTracksList() {
        $init_tracks = $this->tracks;
        $folders = $this->folders;
        $tracks = $init_tracks;
        $tracks = $tracks->filter(function($track) {
            return $track->upload_status == "STATUS_READY";
        });
        foreach ($folders as $folder) {
            $folder_tracks = $folder->tracks->filter(function($track) {
                return $track->upload_status == "STATUS_READY";
            });
            $tracks = $tracks->merge($folder_tracks);
        }
        return $tracks;
    }

    public function getTracksCount() {
        return count($this->getTracksList());
    }
}
