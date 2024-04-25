<?php

namespace App\Models;

use App\Notifications\Types\BaseNotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification {

    use SoftDeletes;

    protected $dates = [
        'deleted_at'
    ];

    protected $hidden = ['notifiable_type', 'notifiable_id'];

    protected $appends = ['is_read']; // , 'notification_data'

    public function getTypeAttribute($type) {
        $type = explode('\\', $type);
        $type = $type[count($type) - 1];
        return $type;
        //$output = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $type));
        //return $output;
    }


    public function getIsReadAttribute() {
        return (bool)$this->read_at;
    }
//
//    public function getNotificationDataAttribute() {
//        if ($this->type == "NewPermissionRequest") {
//            $channel_id = $this->data['data']['entity_id'];
//            $permissions = UserChannelPermissions::where(['user_id' => auth()->user()->id, 'channel_id' => $channel_id])->first();
//            $notification = [
//                'channel' => Channel::find($channel_id),
//                'user' => User::select(['id', 'username'])->find($this->data['user']['id']),
//                'permissions' => $permissions,
//            ];
//            return $notification;
//        } elseif ($this->type == "ProgramIsOnline") {
//            return $this->data;
//        } elseif ($this->type == "ChannelNewFeedPost") {
//            return $this->data;
//        } elseif ($this->type == "PlaylistNewRecording") {
//            return $this->data;
//        } elseif ($this->type == "ChannelNewRecording") {
//            return $this->data;
//        } elseif ($this->type == "ChannelGotOnline") {
//            $data = $this->data;
//            $data['time'] = $this->created_at->timestamp;
//            return $data;
//        }
//
//    }
}
