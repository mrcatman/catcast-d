<?php

namespace App\Models;

use App\Enums\PrivacyStatuses;
use App\Models\SettingsModels\Common\PrivacySettingsModel;
use App\Traits\HasPrivacyStatus;
use App\Traits\HasSettings;
use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPictures;
use App\Traits\HasColors;

class Playlist extends Model {

    use HasPictures;
    use HasColors;
    use HasSettings;
    use HasTags;
    use HasPrivacyStatus;

    protected $table = 'playlists';

    protected $guarded = [];
    protected $appends = [
        'tags',
        'pictures_data',
        'logo', 'banner', 'background', 'player_background',
        'local_url',
        'privacy_status_name'
    ];

    public $pictures_fields = ['logo', 'banner', 'background', 'player_background'];

    protected $additional_settings_models = [
        PrivacySettingsModel::class
    ];
    protected $casts = [
        'links' => 'array' // todo: move to model
    ];

    public static function getEntityType() {
        return 'playlists';
    }

    public function getPlayerBackgroundAttribute(){
        return $this->getPicture('player_background', false);
    }

    public function getBannerAttribute() {
        return $this->getPicture('banner', false);
    }

    public function getBackgroundAttribute() {
        return $this->getPicture("background", false);
    }

    public function getLogoAttribute() {
        $logo = $this->getPicture("logo", false);
        if (!$logo) {
            $media = $this->visibleMedia()->orderBy('created_at', 'desc')->first();
            if ($media && $media->thumbnail) {
                return $media->thumbnail->full_url;
            }
        }
        return $logo;
    }

    public function scopeSearch($filter, $search) {
        return $filter->where(function($query) use ($search) {
            $query->where('name', 'LIKE', '%'.$search.'%');
            $query->orWhere('description', 'LIKE', '%'.$search.'%');
        });
    }

    public function likes(){
        return $this->hasMany(Like::class, 'entity_id', 'id')->with('author:id,username,avatar')->where('entity_type','=','comments');
    }


    public function channel() {
        return $this->hasOne(Channel::class, 'id', 'channel_id');
    }

    public function media() {
        return $this->belongsToMany(Media::class, 'playlists_media')->orderBy('index')->withPivot('index');
    }

    public function visibleMedia() {
        return $this->media()->whereIn('privacy_status', [ PrivacyStatuses::PUBLIC, PrivacyStatuses::UNLISTED]);
    }

    public function getLocalUrlAttribute() {
        return '/playlists/'.$this->uuid;
    }

}
