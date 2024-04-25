<?php
namespace App\Models;
use App\Helpers\ConfigHelper;
use App\Traits\HasTags;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Parsedown;

class Broadcast extends Model {

    use HasTags;

    protected $guarded = [];
    protected $with = ['category', 'user:id,username'];
    protected $appends = ['playback_url', 'thumbnail_url', 'display_description', 'is_online', 'tags'];
    protected $casts = [
        'will_start_at' => 'datetime',
        'will_end_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public static function getEntityType() {
        return 'broadcasts';
    }

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(BroadcastCategory::class);
    }

    public function getIsOnlineAttribute() {
        return $this->started_at && !$this->ended_at;
    }

    public function getPlaybackUrlAttribute() {
        return ConfigHelper::streamsURL().'/live/hls/'.$this->channel_id.'/index.m3u8';
    }

    public function getThumbnailUrlAttribute() {
        return ConfigHelper::streamsURL().'/live/thumbnails/'.$this->channel_id.'.png?'.time();
    }

    public function scopeFinished($query) {
        return $query->whereNotNull('ended_at');
    }

    public function scopePlanned($query) {
        return $query->whereDate('will_start_at', '>', Carbon::now())->whereNull('started_at')->whereNull('ended_at');
    }

    public function scopeOnline($query) {
        return $query->whereNotNull('started_at')->whereNull('ended_at');
    }

    public function scopeNotActive($query) {
        return $query->whereNotNull('ended_at')->orWhere(function($q) {
            $q->whereNotNull('will_start_at')->whereNull('started_at')->whereNull('ended_at');
        });
    }

    public function getDisplayDescriptionAttribute() {
        return app()->make(Parsedown::class)->setBreaksEnabled(true)->text($this->description);
    }

}
