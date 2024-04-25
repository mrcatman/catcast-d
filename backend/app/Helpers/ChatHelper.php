<?php

namespace App\Helpers;

use App\Events\Chat\ChatChangeGuestStateEvent;
use App\Events\Chat\ChatChangeUserStateEvent;
use App\Models\Channel;
use App\Models\IPBan;
use App\Models\UserBan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ChatHelper {

    public static function authenticate($user, $channel_id) {
        $channel = Channel::find($channel_id);
        if (!$channel) {
            return false;
        }
        if ($user) {
            $ip = $user->ip_address;

            if ($user->is_guest) {
                $permissions = [];
                $is_banned = IPBan::where([
                        'channel_id' => $channel->id,
                        'ip_address' => $ip
                    ])->where(function ($q) {
                        $q->whereDate('banned_till', '>=', time());
                        $q->orWhereNull('banned_till');
                    })->count() > 0;
            } else {
                $permissions = PermissionsHelper::getForChannel($channel, $user);
                $is_banned = UserBan::where([
                        'channel_id' => $channel->id,
                        'user_id' => $user->id
                    ])->where(function ($q) {
                        $q->whereDate('banned_till', '>=', time());
                        $q->orWhereNull('banned_till');
                    })->count() > 0;
            }
            return [
                'id' => $user->id,
                'guest_id' => $user->guest_id,
                'is_guest' => $user->is_guest,
                'is_banned' => $is_banned,
                'username' => $user->username,
                'permissions' => $permissions,
                'color' => $user->color,
            ];
        } else {
            return false;
        }
    }

    public static function parseColor($color_string) {
        $color = '#fff';
        if ($color_string) {
            preg_match_all('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $color_string, $matches);
            if (count($matches) > 0) {
                $color = is_array($matches[0]) ? $matches[0][0] : $matches[0];
            }
        }
        return $color;
    }


    public static function canChangeBanState($channel, $user_to_ban) {
        if (auth()->user()->id == $user_to_ban->id) {
            return false;
        }
        $my_permissions = PermissionsHelper::getForChannel($channel);
        $user_permissions = PermissionsHelper::getForChannel($channel, $user_to_ban);
        return (
            (in_array('channel_admin', $my_permissions) && !(in_array('channel_admin', $my_permissions)))
            || in_array('admin', $my_permissions)
            || in_array('owner', $my_permissions)
            || (in_array('moderation', $my_permissions) && count($user_permissions) == 0)
        );
    }

    public static function changeUserBanState($channel, $user_to_ban, $state, $reason = '') {
        $user = auth()->user();
        if ($state) {
            $ban = UserBan::firstOrNew([
                'user_id' => $user_to_ban->id,
                'channel_id' => $channel->id,
            ]);
            $ban->reason = $reason;
            $ban->banned_by = $user->id;
            $ban->save();
        } else {
            UserBan::where(['channel_id' => $channel->id, 'user_id' => $user_to_ban->id])->delete();
        }

        $me = self::getCurrentConnectedUser($channel->id);
        $user = self::getConnectedUserAndUpdate($channel->id, ['id' => $user_to_ban->id], ['is_banned' => $state]);

        $data = ['is_banned' => $state];
        broadcast(new ChatChangeUserStateEvent($channel, $me, $user, $data));
    }

    public static function changeGuestBanState($channel, $guest_id, $ip_address_to_ban, $state, $reason = '') {
        $user = auth()->user();

        if ($state) {
            $ban = IPBan::firstOrNew([
                'ip_address' => $ip_address_to_ban,
                'channel_id' => $channel->id,
            ]);
            $ban->banned_by = $user->id;
            $ban->reason = $reason;
            $ban->save();
        } else {
            IPBan::where(['channel_id' => $channel->id, 'ip_address' => $ip_address_to_ban])->delete();
        }
        $me = ChatHelper::getCurrentConnectedUser($channel->id);
        if ($guest_id) {
            $guest = ChatHelper::getConnectedUserAndUpdate($channel->id, ['guest_id' => $guest_id], ['is_banned' => $state]);
            broadcast(new ChatChangeGuestStateEvent($channel, $me, $guest, ['is_banned' => $state]));
        }
    }

    public static function getValidUsername($channel, $username) {
    //    $existing_
// todo: get users, check if username is occupied, add random number
        $settings = $channel->additional_settings['chat'];
        $guest_username = request()->header('X-Guest-Username') ? urldecode(request()->header('X-Guest-Username')) : $settings['default_guest_username']. rand(1, 999); // todo: maybe remove
        return $guest_username;
    }

    public static function authorizeGuest($channel, $username) {
        $guest_id = uniqid('', true);
        $access_key = Str::random(24);

        $username = self::getValidUsername($channel, $username);
        Cache::remember('chat.guest.'.$guest_id, 86400, function() use ($channel, $access_key, $username) {
            return [
                'channel_id' => $channel->id,
                'access_key' => $access_key,
                'ip_address' => request()->ip(),
                'username' => $username
            ];
        });
        return ['guest_id' => $guest_id, 'guest_access_key' => $access_key];
    }

    public static function getGuestData($guest_id) {
        return Cache::get('chat.guest.'.$guest_id);
    }

    public static function getGuestIdFromRequest($channel_id) {
        if (request()->has('guest_id') && request()->has('access_key')) {
            $data = self::getGuestData(request()->input('guest_id'));
            if ($data && $data['channel_id'] == $channel_id && $data['access_key'] == request()->input('access_key')) {
                return request()->input('guest_id');
            }
        }
        return null;
    }

    public static function getCurrentConnectedUser($channel_id) {
        $user = auth()->user();
        $guest_id = null;
        if (!$user && request()->has('access_key')) {
            $guest_id = self::getGuestIdFromRequest($channel_id);
        }
        return self::getConnectedUserAndUpdate($channel_id, $user ? ['id' => $user->id] : ['guest_id' => $guest_id]);
    }

    public static function getConnectedUserAndUpdate($channel_id, $find_by, $update = null) { // todo: change update
        $connected_users = self::getChatUsers($channel_id);

        $connected_user = null;

        foreach ($connected_users as &$user_obj) {
            if (
                isset($find_by['id']) && ($find_by['id'] == $user_obj->user_info->id && !$user_obj->user_info->is_guest) ||
                isset($find_by['guest_id']) && $find_by['guest_id']  == $user_obj->user_info->guest_id && $user_obj->user_info->is_guest
            ){
                $connected_user = $user_obj->user_info;
                if ($update) {
                    foreach ($update as $key => $val) {
                        $user_obj->user_info->{$key} = $val;
                    }
                }
            }
        }
        if ($update) {
            self::setChatUsers($channel_id, $connected_users);
        }
        return $connected_user;
    }


    public static function getChatUsers($channel_id) {
        $users = Redis::get('presence-App.Chat.'.$channel_id.':members');
        $users = json_decode($users);
        if (!$users) {
            $users = [];
        }
        return array_values($users);
    }

    public static function setChatUsers($channel_id, $users) {
        Redis::set('presence-App.Chat.'.$channel_id.':members', json_encode($users));
    }
}
