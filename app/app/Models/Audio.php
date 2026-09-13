<?php

namespace App\Models;

use App\Models\RadioServer;
use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class Audio extends Model
{
    use HasTags;

    public $table = 'radio_files';

    public $appends = ["client_path"];

    public $fillable = ["author","description","title","album"];

    protected $entity_type = 'tracks';

    protected $casts = [
        'waveform_data' => 'array',
    ];

    public $statuses = [
        -1 => "STATUS_PROCESSING",
        0 => "STATUS_FAIL",
        1 => "STATUS_READY"
    ];

    public function site_folder() {
        return $this->belongsTo('App\Models\Folder','folder_id','id');
    }

    public function getRadioServerPathAttribute() {
        $path = $this->attributes['radio_server_path'];
        if (strlen($path) > 0 && $path[0] !== "/") {
            $path = "/".$path;
        }
        return $path;
    }

    public function getClientPathAttribute() {
        $server = RadioServer::find($this->radio_server);
        if (!$server) {
            return "";
        }
        $domain = "https://".$server->ip_address;
        $path = $domain."/stations/".$this->channel_id."/".$this->filename;
        return $path;
    }

    public function getUploadStatusAttribute($upload_status) {
        $status = $this->statuses[$upload_status];
        return $status;
    }

    public function channel() {
        return $this->hasOne('App\Models\Channel', 'id', 'channel_id');
    }

    public function playlist() {
        return $this->belongsTo('App\Models\Playlist','playlist_id','playlistid');
    }

    public function user() {
        return $this->belongsTo('App\Models\User','user_id','id');
    }


    public function deleteTempFile() {
        Storage::disk("public_uploads")->delete("uploads_temp/tmp_".$this->id.".mp3");
    }



    public function playlists() {
        return $this->belongsToMany('App\Models\RadioPlaylist','radio_files_playlists','radio_file_id','playlist_id');
    }

    public function getFoldersAttribute($folders) {
        if ($folders && $folders !== "") {
            $folders = json_decode($folders);
            if (is_array($folders)) {
                return $folders;
            }
            return [];
        }
        return [];
    }

    public function setFoldersAttribute($folders) {
        foreach ($folders as &$folder_id) {
            $folder_id = intval($folder_id);
        }
        $this->attributes['folders'] = json_encode($folders,JSON_UNESCAPED_UNICODE);
    }

    public function scopeSearch($filter, $search) {
        return $filter->where(function($query) use ($search) {
            $query->where('title', 'LIKE', '%'.$search.'%');
            $query->orWhere('description', 'LIKE', '%'.$search.'%');
        });
    }

    public function scopeVisible($filter) {
        return $filter->where(function($query) {
            $visible_folders = Folder::where(['is_public' => 1])->pluck('id');
            $query->where(['is_public' => 1]);
            $query->orWhereIn('folder_id', $visible_folders);
        });
    }

    public function scopefromNotBlockedChannels($query) {
        $channels = Channel::whereNull('blocked_at')->pluck('id');
        return $query->whereNotIn('channel_id', $channels);
    }


    public function scopeFromLikedChannels($query) {
        if ($user = auth()->user()) {
            $channels = Like::where(['id'=>$user->id,'entity_type'=>'channels'])->get()->pluck('entity_id');
            return $query->whereIn('channel_id', $channels);
        }
        return $query;
    }

    public function scopeFromLikedChannelsAndPlaylists($query) {
        if ($user = auth()->user()) {
            $channels = Like::where(['id'=>$user->id,'entity_type'=>'channels'])->get()->pluck('entity_id');
            $playlists = Like::where(['id'=>$user->id,'entity_type'=>'playlists'])->get()->pluck('entity_id');
            $folders = Folder::whereIn('connected_playlist_id', $playlists)->pluck('id');
            $query = $query->where(function ($q) use ($channels, $folders) {
                $q->whereIn('channel_id', $channels)
                    ->orWhereIn('folder_id', $folders);
            });
            return $query;
        }
        return $query;
    }

    public function scopeRecommended($query) {
        if ($user = auth()->user()) {
            $channels = Like::where(['id'=>$user->id,'entity_type'=>'channels'])->get()->pluck('entity_id');
            $tags = Tag::where(['entity_type'=>'channels'])->whereIn('entity_id', $channels)->pluck('tag')->unique();
            $extended_channels = Tag::where(['entity_type'=>'channels'])->whereIn('tag', $tags)->whereNotIn('entity_id', $channels)->pluck('entity_id')->unique();
            $query = $query->whereIn('channel_id', $extended_channels);
            $query = $query->orderBy('created_at', 'desc');
            return $query;
        }
        return $query;
    }

    public function scopePopular($query) {
        $days_count = 90;
        $time_start = time() - $days_count * 24 * 60 * 60;
        $query = $query->orderBy('views', 'DESC');
        $query->where('created_at', '>=', $time_start);
        return $query;
    }


    public function scopeMostListened($query) {
        $query = $query->orderBy('views', 'DESC');
        return $query;
    }

    public function scopeBest($query) {
        $query = $query->orderBy('likes_count', 'DESC');
        return $query;
    }


    public function scopeNowWatching($query) {
        $viewed = StatisticsSession::where(['type_id' => $this->statistics_type_id])->orderBy('id', 'desc')->pluck('entity_id')->unique();
        $viewed = $viewed->toArray();
        $query = $query->whereIn('id', $viewed);
        $query = $query->orderByRaw('FIND_IN_SET (id, "'.implode(", ", $viewed).'") DESC');
        return $query;
    }

    public function deleteFromStorage() {
        $this->deleteTempFile();
        $storage = Storage::disk('ftp_' . $this->radio_server);
        $storage->delete($this->radio_server_path);
    }

}
