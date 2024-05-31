<?php

namespace App\Models;


use App\Models\SettingsModels\Audio\AudioMetadataSettingsModel;
use App\Models\SettingsModels\Common\PrivacySettingsModel;
use App\Traits\HasPrivacyStatus;
use App\Traits\HasSettings;
use App\Traits\HasTags;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Media extends Model {

    use HasTags;
    use HasSettings;
    use HasPrivacyStatus;

    protected $additional_settings_models = [
        PrivacySettingsModel::class,
        AudioMetadataSettingsModel::class
    ];

    public $appends = [
        'tags',
        'type_name',
        'source_type_name',
        'upload_ready',
        'local_url',
        'privacy_status_name'
    ];

    public $with = ['thumbnail', 'gridThumbnail'];
    protected $guarded = [];

    const TYPE_VIDEO = 0;
    const TYPE_AUDIO = 1;
    const TYPE_NAMES_MAP = [
        'video' => self::TYPE_VIDEO,
        'audio' => self::TYPE_AUDIO
    ];

    const SOURCE_TYPE_UPLOAD = 0;
    const SOURCE_TYPE_RECORD = 1;
    const SOURCE_TYPE_NAMES_MAP = [
        'upload' => self::SOURCE_TYPE_UPLOAD,
        'record' => self::SOURCE_TYPE_RECORD
    ];

    public static function getEntityType() {
        return 'media';
    }

    public function scopeOfSelectedType($filter) {
        if (request()->has('type') && isset(self::TYPE_NAMES_MAP[request()->input('type')])) {
            return $filter->where(function($query) {
                $query->where(['media_type' => self::TYPE_NAMES_MAP[request()->input('type')]]);
            });
        }
        return $filter;
    }

    public static function getPreferredType($channel) {
        if ($channel->channel_type === Channel::TYPE_RADIO) {
            return self::TYPE_AUDIO;
        }
        return self::TYPE_VIDEO;
    }

    public function getTypeNameAttribute() {
        $map = array_flip(self::TYPE_NAMES_MAP);
        return $map[$this->media_type];
    }

    public function getSourceTypeNameAttribute() {
        $map = array_flip(self::SOURCE_TYPE_NAMES_MAP);
        return $map[$this->source_type];
    }

    public function scopeSearch($filter, $search) {
        return $filter->where(function($query) use ($search) {
            $query->where('title', 'LIKE', '%'.$search.'%');
            $query->orWhere('description', 'LIKE', '%'.$search.'%');
        });
    }

    public function scopeFilterSubscriptions($query) {
        if ($user = auth()->user()) {
            $channel_ids = Like::where(['user_id' => $user->id, 'entity_type' => Channel::getEntityType()])->get()->pluck('entity_id');
            $playlist_ids = Like::where(['user_id' => $user->id, 'entity_type'=> Playlist::getEntityType()])->get()->pluck('entity_id');
            $playlists_media_ids = DB::table('playlists_media')->whereIn('playlist_id', $playlist_ids)->pluck('media_id');
            $query = $query->where(function ($q) use ($channel_ids, $playlists_media_ids) {
                $q->whereIn('channel_id', $channel_ids);
                $q->orWhereIn('id', $playlists_media_ids);
            });
            return $query;
        }
        return $query;
    }

    public function scopeFilterPopular($query) {
        return $query->withCount('statisticsSessionsInLastDays')->orderBy('statistics_sessions_in_last_days_count', 'desc');
    }

    public function statisticsSessions()
    {
        return $this->hasMany(StatisticsSession::class, 'entity_id', 'id')->where(['entity_type' => self::getEntityType()]);
    }

    public function statisticsSessionsInLastDays()
    {
        return $this->statisticsSessions()->whereDate('created_at', '>=', Carbon::now()->subDays(5));
    }

    public function scopeRecommended($query) {
        if ($user = auth()->user()) { // todo: recommend based on users likes
            $channels = Like::where(['id'=>$user->id,'entity_type'=>'channels'])->get()->pluck('entity_id');
            $tags = Tag::where(['entity_type'=>'channels'])->whereIn('entity_id', $channels)->pluck('tag')->unique();
            $extended_channels = Tag::where(['entity_type'=>'channels'])->whereIn('tag', $tags)->whereNotIn('entity_id', $channels)->pluck('entity_id')->unique();
            $query = $query->whereIn('channel_id', $extended_channels);
            $query = $query->orderBy('created_at', 'desc');
            return $query;
        }
        return $query;
    }

    public function scopeFromNotBlockedChannels($query) {
        $blocked_channel_ids = Channel::whereNotNull('blocked_at')->pluck('id');
        return $query->whereNotIn('channel_id', $blocked_channel_ids);
    }

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function files() {
        return $this->hasMany(MediaFile::class)->orderBy('quality', 'DESC');
    }

    public function thumbnail() {
        return $this->hasOne(Picture::class, 'id', 'thumbnail_id');
    }

    public function gridThumbnail() {
        return $this->hasOne(VideoGridThumbnail::class, 'video_id', 'id');
    }

    public function likes() {
        return $this->hasMany(Like::class,'entity_id')->where('likes.entity_type', '=', 'records');
    }

    public function playlists() {
        return $this->belongsToMany(Playlist::class, 'playlists_media');
    }

    public function playlistsOfHomeChannel() {
        return $this->belongsToMany(Playlist::class, 'playlists_media')->where(['playlists.channel_id' => $this->channel_id]);
    }

    public function getPlaylistIdsAttribute() {
        return $this->playlists->pluck('id');
    }

    public function getDescriptionAttribute() {
        $text =  $this->attributes['description'];
        $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
        $text = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $text);
        return $text;
    }

    public function getUploadReadyAttribute() {
        return count($this->files) > 0;
    }

    public function getGridThumbnailUrlAttribute() {
        if ($this->gridThumbnail) {
            return  "http://".$this->server."/".$this->getFolder()."/".$this->gridThumbnail->url;
        }
        return null;
    }

    public function folder() {
        return $this->belongsTo(MediaFolder::class);
    }


    public function getTotalFilesSizeAttribute() {
        return $this->files->sum('file_size');
    }

    public function getLocalUrlAttribute() {
        return '/media/'.$this->uuid;
    }

}
