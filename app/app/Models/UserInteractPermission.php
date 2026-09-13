<?php

namespace App\Models;

use App\Notifications\ChannelGotOnline;
use App\Notifications\NewPermissionRequest;
use Illuminate\Database\Eloquent\Model;

class UserInteractPermission extends Model
{

    public const STATUS_PENDING = -1;
    public const STATUS_BLOCKED = 0;
    public const STATUS_ALLOWED = 1;

    public $table = 'users_interact_permissions';

    protected $fillable = ['user_id', 'entity_id', 'entity_type', 'status'];

    protected $appends = ['entity_name', 'entity_picture'];

    public function user() {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public static function checkStatus($user_id, $entity_id, $entity_type, $do_not_notify = false, $do_not_add = false) {
        return self::STATUS_ALLOWED; // todo: interact permissions
        $permission = self::firstOrNew(['user_id' => $user_id, 'entity_id' => $entity_id, 'entity_type' => $entity_type]);
        if (!$permission->exists) {
            if (!$do_not_add) {
                $permission->status = self::STATUS_PENDING;
                $permission->save();
                if (!$do_not_notify) {
                    (new NewPermissionRequest($permission))->sendToUser($user);
                }
            }
            return self::STATUS_PENDING;
        }
        return $permission->status;
    }

    public function getEntityNameAttribute() {
        if ($this->entity_type == "channels") {
            return Channel::find($this->entity_id)->name;
        }
        return "";
    }

    public function getEntityPictureAttribute() {
        if ($this->entity_type == "channels") {
            return Channel::find($this->entity_id)->logo;
        }
        return "";
    }

}
