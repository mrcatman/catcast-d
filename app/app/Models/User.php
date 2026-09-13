<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Traits\HasLinks;
use App\Traits\HasPictures;
use App\Traits\HasSettings;

use App\Models\SettingsModels\User\PrivacySettingsModel;


class User extends Authenticatable implements JWTSubject {

    use HasFactory;
    use Notifiable;
    use HasPictures;
    use HasSettings;
    use HasLinks;

    protected $table = 'users';

    protected $fillable = [
        'username', 'full_name', 'about', 'status_text'
    ];

    protected $hidden = [
        'password', 'private_key', 'ip_address'
    ];

    protected $appends = [
        'avatar', 'pictures_data', 'is_admin'
    ];

    protected $pictures_fields = ['avatar'];

    protected $additional_settings_models = [
        PrivacySettingsModel::class,
    ];

    const ROLE_ID_USER = 1;
    const ROLE_ID_ADMIN = 255;

    public static function getEntityType() {
        return 'users';
    }

    public function getIsAdminAttribute() {
        return $this->id === self::ROLE_ID_ADMIN;
    }

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return ['username'];
    }

    public function getAvatarAttribute() {
        return $this->getPicture('avatar', "/assets/pictures/no-avatar.svg");
    }

    public function getPrivacySettingsAttribute() {
        if ($privacy = $this->private) {
            $privacy_data = [
                'page' => $privacy[0],
                'feed' => $privacy[1],
                'messages' => $privacy[2]
            ];
            return $privacy_data;
        }
        return null;
    }


    //public function socialConnections() {
    //    return $this->hasMany('App\Models\SocialConnection');
    //}

    public function registrationRequest() {
        return $this->hasOne(UserRegistrationRequest::class);
    }

    public function notifications(){
        return $this->morphMany(Notification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    public function getUnreadNotificationsCountAttribute() {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function info() {
        return $this->hasMany(UserSetting::class);
    }

    public function likes() {
        return $this->hasMany(Like::class);
    }


    public function getRegistrationRequestApprovedAttribute() {
        return !$this->registrationRequest || $this->registrationRequest->approved_at;
    }

    public function getBanAttribute() { // todo: change attr name
        if (!$user = auth()->user()) {
            return null;
        }
        return BlacklistItem::where(['from_id' => $this->id, 'to_id' => $user->id])->count() > 0;
    }

    public function checkPrivacySettings($type) {
        $value = $this->additional_settings['privacy'][$type];
        return (auth()->user() && auth()->user()->id == $this->id)
        || !$this->ban && ($value === 'all' || ($value === 'friends') && $this->friend_state === 'STATE_IN_FRIENDS');
    }

    public function getBannedAttribute() {
        if (!$user = auth()->user()) {
            return false;
        }
        return BlacklistItem::where(['from_id' => $this->id, 'to_id' => $user->id])->count() > 0;
    }

    public function accountConnections() {
        return $this->hasMany(AccountConnection::class);
    }

    public function getEmailConfirmedAttribute() {
        return $this->accountConnections()->where(['account_type' => 'email', 'confirmed' => true])->count() > 0;
    }

    public function routeNotificationForMail($notification) {
        $connection = $this->accountConnections()->where(['account_type' => 'email'])->first();
        if ($connection) {
            return $connection->account_name;
        }
        return '';
    }

    public function getTelegramIdAttribute() {
        $connection = $this->accountConnections()->where(['account_type'=>'telegram', 'confirmed'=> true])->first();
        if ($connection) {
            return $connection->account_name;
        }
        return null;
    }

    public function scopeSearch($filter, $search) {
        return $filter->where(function($query) use ($search) {
            $query->where('username', 'LIKE', '%'.$search.'%');
        });
    }

    public function receivesBroadcastNotificationsOn() {
        return 'App.User.'.$this->id;
    }


    public function getWebUrlAttribute() {
        return ($this->domain ? 'https://'.$this->domain : config('site.domains.web')).'/users/'.$this->username;
    }

    public function getActorUrlAttribute($suffix = '') {
        return ($this->domain ? 'https://'.$this->domain : config('site.domains.web')).'/api/federation/users/'.$this->username.$suffix;
    }


}
