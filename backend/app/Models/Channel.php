<?php

namespace App\Models;

use App\Autopilot\AutopilotRepository;

use App\Helpers\ConfigHelper;
use App\Models\SettingsModels\Channel\DefaultBroadcastMetadataSettingsModel;
use App\Models\SettingsModels\Channel\RecordSettingsModel;
use App\Traits\HasLinks;
use App\Traits\HasSettings;
use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasPictures;
use App\Traits\HasColors;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\SettingsModels\Channel\ChatSettingsModel;
use App\Models\SettingsModels\Channel\PrivacySettingsModel;
use App\Models\SettingsModels\Channel\DisplaySettingsModel;
use App\Models\SettingsModels\Channel\DonatesSettingsModel;
use App\Models\SettingsModels\Channel\BroadcastSettingsModel;


class Channel extends Model
{
    use HasPictures;
    use HasColors;
    use HasTags;
    use HasSettings;
    use HasLinks;

    use SoftDeletes;

    const TYPE_TV = 0;
    const TYPE_RADIO = 1;
    const TYPE_NAMES_MAP = [
        'tv' => self::TYPE_TV,
        'radio' => self::TYPE_RADIO
    ];
    const DEFAULT_TYPE = self::TYPE_TV;

    protected $appends = [
        'type_name',
        'is_radio',
        'tags',
        'pictures_data',
        'logo', 'banner', 'background', 'player_background',
        'local_url',
        'active_broadcast'
    ];

    protected $guarded = ['user_id', 'views'];
    protected $hidden = ['private_key', 'stream_password'];
    protected $with = ['pictures'];
    protected $dates = ['last_online_at'];

    protected $pictures_fields = ['logo', 'banner', 'background', 'player_background'];

    protected $additional_settings_models = [
        DonatesSettingsModel::class,
        BroadcastSettingsModel::class,
        DisplaySettingsModel::class,
        ChatSettingsModel::class,
        PrivacySettingsModel::class,
        RecordSettingsModel::class,
        DefaultBroadcastMetadataSettingsModel::class
    ];

    public static function getEntityType() {
        return 'channels';
    }


    public function getChannelTypeAttribute($type) {
        if (!$type) {
            return self::DEFAULT_TYPE;
        }
        return $type;
    }

    public function getTypeNameAttribute() {
        $map = array_flip(self::TYPE_NAMES_MAP);
        return $map[$this->channel_type];
    }

    public function getOccupiedDiskSpace() {
        return (int)$this->media->sum('total_files_size');
    }

    public function getTotalDiskSpace() { // todo: admin panel with settings
        return ConfigHelper::diskSpace($this->type_id);
    }


    public function getIsRadioAttribute() {
        return $this->channel_type === self::TYPE_RADIO;
    }

    public function getPlayerBackgroundAttribute(){
        return $this->getPicture('player_background', false);
    }

    public function getBannerAttribute() {
        return $this->getPicture('banner', false);
    }

    public function getBackgroundAttribute() {
        return $this->getPicture('background', false);
    }


    public function getLogoAttribute() {
        return $this->getPicture('logo', false);
    }

    public function getDescriptionTextAttribute($description) {
        $text = strip_tags(html_entity_decode($description));
        return $text;
    }

    public function scopeNotBlocked($query) {
        return $query->whereNull('blocked_at');
    }

    public function scopeVisible($query) { // todo: change
        return $query;
    }

    public function scopeOfSelectedType($query, $type) {
        if (isset(self::TYPE_NAMES_MAP[$type])) {
            return $query->where('channel_type', '=', self::TYPE_NAMES_MAP[$type]);
        }
        return $query;
    }

    public function scopeSearch($filter, $search) {
        return $filter->where(function($query) use ($search) {
            $query->where('name', 'LIKE', '%'.$search.'%');
            $query->orWhere('shortname', 'LIKE', '%'.$search.'%');
            $query->orWhere('description', 'LIKE', '%'.$search.'%');
        });
    }

    public function scopeForUser($query, User $user) {
        $channel_ids_team = UserChannelPermissions::where(['user_id' => $user->id])->pluck('channel_id');
        return $query->where(function($q) use ($user, $channel_ids_team) {
            $q->whereIn('id',$channel_ids_team);
            $q->orWhere('user_id', $user->id);
        });
    }

    public function scopeFilterOnline($query) {
        return $query->has('active_broadcasts', '>', 0);
    }

    public function scopeFilterNew($query) {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeFilterSubscriptions($query) {
        $user = auth()->user();
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }
        return $query->whereIn('id', $user->likes()->where(['entity_type' => 'channels'])
            ->pluck('entity_id'))
            ->withCount('active_broadcasts')->orderBy('active_broadcasts_count', 'desc');
    }


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function media() {
        return $this->hasMany(Media::class);
    }


    public function playlists() {
        return $this->hasMany(Playlist::class);
    }

    public function team() {
        return $this->hasMany(UserChannelPermissions::class);
    }

    public function likes() {
        return $this->hasMany(Like::class,'entity_id')->where('likes.entity_type', '=', 'channels');
    }

    public function broadcasts() {
        return $this->hasMany(Broadcast::class, 'channel_id', 'id')->orderBy('updated_at', 'desc');
    }

    public function active_broadcasts() {
        return $this->broadcasts()->whereNotNull('started_at')->whereNull('ended_at');
    }

    public function getActiveBroadcastAttribute() {
        return $this->active_broadcasts()->orderBy('id', 'desc')->first();
    }

    public function getLocalUrlAttribute() {
        return '/'.$this->shortname;
    }

    public function getWebUrlAttribute() {
        return 'https://'.($this->domain ? $this->domain : config('site.domain')).'/'.$this->shortname;
    }

    public function getActorUrlAttribute($suffix = '') {
        return 'https://'.($this->domain ? $this->domain : config('site.domain')).'/api/federation/channels/'.$this->shortname.$suffix;
    }


}
