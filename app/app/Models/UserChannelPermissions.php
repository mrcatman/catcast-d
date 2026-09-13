<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserChannelPermissions extends Model {

    const PERMISSIONS_STANDARD = 1;
    const PERMISSIONS_FULL = 2;

    const LIST = [
            [
                'id' => 'owner',
                'title' => 'channel_permissions.owner.name',
                'description' => 'channel_permissions.owner.description',
                'can_be_added' => false,
            ],
            [
                'id' => 'channel_admin',
                'title' => 'channel_permissions.channel_admin.name',
                'description' => 'channel_permissions.channel_admin.description',
            ],
            [
                'id' => 'statistics',
                'title' => 'channel_permissions.statistics.name',
                'description' => 'channel_permissions.statistics.description',
            ],
            [
                'id' => 'edit_info',
                'title' => 'channel_permissions.edit_info.name',
                'description' => 'channel_permissions.edit_info.description',
            ],
            [
                'id' => 'moderation',
                'title' => 'channel_permissions.moderation.name',
                'description' => 'channel_permissions.moderation.description',
                'can_be_full' => true,
            ],
            [
                'id' => 'team',
                'title' => 'channel_permissions.team.name',
                'description' => 'channel_permissions.team.description',
                'can_be_full' => true,
            ],
            [
                'id' => 'live_broadcast',
                'title' => 'channel_permissions.live_broadcast.name',
                'description' => 'channel_permissions.live_broadcast.description',
                'can_be_full' => true,
            ],
            [
                'id' => 'autopilot',
                'title' => 'channel_permissions.autopilot.name',
                'description' => 'channel_permissions.autopilot.description',
                'can_be_full' => true,
            ],
            [
                'id' => 'donates',
                'title' => 'channel_permissions.donates.name',
                'description' => 'channel_permissions.donates.description',
                'can_be_full' => true,
            ],

            [
                'id' => 'news',
                'title' => 'channel_permissions.news.name',
                'description' => 'channel_permissions.news.description',
                'can_be_full' => true,
            ],
            [
                'id' => 'media',
                'title' => 'channel_permissions.media.name',
                'description' => 'channel_permissions.media.description',
                'can_be_full' => true,
            ],
            [
                'id' => 'playlists',
                'title' => 'channel_permissions.playlists.name',
                'description' => 'channel_permissions.playlists.description',
                'can_be_full' => true,
            ],
    ];

    use SoftDeletes;

    protected $table = 'users_channels_permissions';

    protected $guarded = [];

    protected $casts = [
        'permissions' => 'array',
    ];

    const USER_ID_KEY = 'added_by_user_id';


    public function user() {
        return $this->belongsTo(User::class);
    }


    public function added_by() {
        return $this->belongsTo(User::class, 'added_by_user_id', 'id');
    }


    public function channel() {
        return $this->belongsTo(Channel::class);
    }


}
