<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Models\IPBan;
use App\Models\User;
use App\Models\UserBan;
use App\Models\UserInteractPermission;
use App\Models\UserChannelPermissions;
use App\Models\Channel;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ChannelBansController extends Controller {

    private function deleteOldBans() {
        UserBan::whereNotNull('banned_till')->whereDate('banned_till', '<', Carbon::now())->delete();
        IPBan::whereNotNull('banned_till')->whereDate('banned_till', '<', Carbon::now())->delete();
    }

    public function getUserBansList($id) {
        $channel = PermissionsHelper::getChannelIfAllowed($id, ['moderation']);

        $user = auth()->user();
        $full_permissions = PermissionsHelper::getStatus(['moderation'], $channel, null, true);

        $this->deleteOldBans();

        $users_bans = UserBan::where(['channel_id' => $channel->id])->orderBy('updated_at', 'desc');
        if (request()->has('search')) {
            $users_bans = $users_bans->whereHas('user', function ($query) {
                return $query->where('username', 'LIKE', '%'.request()->input('search').'%');
            });
        }
        $users_bans = $users_bans->paginate(request()->input('count', 24));
        $users_bans->getCollection()->transform(function($item) use ($user, $full_permissions) {
            $item->can_delete = $full_permissions || $user->id == $item->banned_by;
            return $item;
        });
        return $users_bans;
    }

    public function getIPBansList($id) {
        $channel = PermissionsHelper::getChannelIfAllowed($id, ['moderation']);

        $user = auth()->user();
        $full_permissions = PermissionsHelper::getStatus(['moderation'], $channel, null, true);

        $this->deleteOldBans();

        $ip_bans = IPBan::where(['channel_id' => $channel->id])->orderBy('updated_at', 'desc');
        if (request()->has('search')) {
            $ip_bans = $ip_bans->whereHas('user', function ($query) {
                return $query->where('ip_address', 'LIKE', '%'.request()->input('search').'%');
            });
        }
        $ip_bans = $ip_bans->paginate(request()->input('count', 24));
        $ip_bans->getCollection()->transform(function($item) use ($user, $full_permissions) {
            $item->can_delete = $full_permissions || $user->id == $item->banned_by;
            return $item;
        });
        return $ip_bans;
    }


    private function fillData($ban, $channel) {
        $ban->reason = request()->input('reason', '');
        $ban->banned_by = auth()->user()->id;
        $ban->channel_id = $channel->id;
        if (request()->input('banned_till') > 0) {
            $ban->banned_till = Carbon::createFromTimestamp(request()->input('banned_till'));
        } elseif (request()->input('ban_duration') > 0) {
            $ban->banned_till = Carbon::createFromTimestamp(time() + request()->input('ban_duration'));
        } else {
            $ban->banned_till = null;
        }
        $ban->save();
    }

    public function updateUserBan($id) {
        $channel = Channel::findOrFail($id);

        $user_to_ban = null;
        if (request()->filled('user_id')) {
            $user_to_ban = User::find(request()->input('user_id'));
        } elseif (request()->filled('username')) {
            $user_to_ban = User::where(['username' => request()->input('username')])->first();
        }
        if (!$user_to_ban) {
            return CommonResponses::validationError([ 'user_id' => ['dashboard.banlist.errors.user_not_found']]);
        }

        $user_ban = UserBan::firstOrNew(['user_id' => $user_to_ban->id, 'channel_id' => $channel->id]);
        PermissionsHelper::check(['moderation'], $channel, $user_ban);

        $permissions = PermissionsHelper::getForChannel($channel, $user_to_ban, false);
        if (count(array_keys($permissions)) > 0) {
            return response()->json([
                'message' => 'dashboard.banlist.user_is_in_channel_team',
            ], 403);
        }

        $user_ban->user_id = $user_to_ban->id;
        $this->fillData($user_ban, $channel);
        return $user_ban;
    }

    public function updateIPBan($id) {
        $channel = Channel::findOrFail($id);

        $ip_address = request()->input("ip_address");
        $ip_ban = IPBan::firstOrNew(['ip_address' => $ip_address, 'channel_id' => $channel->id]);
        PermissionsHelper::check(['moderation'], $channel, $ip_ban);

        $valid = filter_var($ip_address, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4) || filter_var($ip_address, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6);
        if (!$valid) {
            return CommonResponses::validationError([ 'ip_address' => ['dashboard.banlist.errors.wrong_ip']]);
        }

        $ip_ban->ip_address = $ip_address;
        $this->fillData($ip_ban, $channel);
        return $ip_ban;
    }

    public function deleteUserBan($id, $user_id) {
        $channel = Channel::findOrFail($id);
        $user_ban = UserBan::where(['user_id' => $user_id, 'channel_id' => $channel->id])->firstOrFail();
        PermissionsHelper::check(['moderation'], $channel, $user_ban);
        $user_ban->delete();
        return $user_ban;
    }




    public function deleteIPBan($id, $ip_address) {
        $channel = Channel::findOrFail($id);
        $ip_ban = IPBan::where(['ip_address' => $ip_address, 'channel_id' => $channel->id])->firstOrFail();
        PermissionsHelper::check(['moderation'], $channel, $ip_ban);
        $ip_ban->delete();
        return $ip_ban;
    }




}
