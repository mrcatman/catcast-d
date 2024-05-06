<?php

namespace App\Http\Controllers;

use App\Helpers\ChatHelper;
use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;

use App\Models\ChatMessage;
use App\Models\Channel;
use App\Models\User;
use App\Models\UserBan;
use App\Models\IPBan;

use App\Events\Chat\ChatMessageDeletedEvent;
use App\Events\Chat\ChatMessageEvent;
use App\Events\Chat\ChatClearEvent;
use App\Events\Chat\ChatChangeUserStateEvent;
use App\Events\Chat\ChatChangeGuestStateEvent;

class ChatController extends Controller {


    public function getMessages($id) {
        // todo: maybe move limit to config
        return ChatMessage::where(['channel_id' => $id])->orderBy('created_at','desc')->limit(100)->get()->reverse()->values();
    }

    public function getConfig($id) {
        $channel = Channel::findOrFail($id);
        return $channel->additional_settings['chat'];
    }


    public function sendMessage($id) {
        $channel = Channel::findOrFail($id);
        $settings = $channel->additional_settings['chat'];
        if ($settings['disabled']) {
            return response()->json(['message' => 'chat.errors.chat_is_off'], 403);
        }
        $user = auth()->user();

        if ( !$user && !$settings['allow_guests']) {
            return response()->json(['message' => 'chat.errors.guests_are_not_allowed'], 403);
        }

        $ip = request()->ip();
        if (!$user) {
            $banned = IPBan::where(['channel_id'=>$channel->id, 'ip_address'=> $ip])->first();
            if ($banned) {
                return response()->json(['message' => 'chat.errors.your_ip_is_banned'], 403);
            }
        } else {
            $banned = UserBan::where(['channel_id'=>$channel->id, 'user_id' => $user->id])->first();
            if ($banned) {
                return response()->json(['message' => 'chat.errors.your_account_is_banned'], 403);
            }
        }
        $text = strip_tags(trim(request()->input('text', '')));
        if (strlen($text) === 0) {
            return response()->json(['message' => 'chat.errors.enter_message'], 422);
        }
        $forbidden_words = $settings['forbidden_words'];
        foreach ($forbidden_words as $word) {
            if (strpos($text, $word['word']) !== false) {
                return response()->json(['message' => 'chat.errors.message_has_forbidden_words'], 422);
            }
        }
        $smiles = $this->getSmilesReplacements($settings);
        foreach ($smiles as $smile => $replacement) {
            $text = str_replace(":$smile:", "<img class='smiley' src='$replacement' />", $text);
        }

        $color = ChatHelper::parseColor(request()->input('color'));

        $message = new ChatMessage([
            'ip_address' => $ip,
            'text' => $text,
            'channel_id' => $channel->id,
            'color' => $color
        ]);

        if (request()->has('reply_to')) {
            $message->reply_to = request()->input('reply_to'); // todo: validate
        }

        if ($user) {
            $message->username = $user->username;
            $message->user_id = $user->id;
        } else {
            $message->username = request()->input('username', $settings['default_guest_username']); //todo: check
        }

        $message->save();
        //ChatMessage::where(['channel_id' => $channel->id])->orderBy('id', 'desc')->limit(10000)->offset(100)->delete();
        broadcast(new ChatMessageEvent($channel, $user, $message));
        return $message;
    }

    protected function getSmilesReplacements($settings) {
        // todo: smiles
        $replacements = [];
        $standard_smileys = config('site.smileys');
        if (!$standard_smileys) {
            $standard_smileys = [];
        }
        foreach ($standard_smileys as $index => $smiley) {
            $replacements[':standard_'.$index.':'] = $smiley['full_url'];
            if (isset($smiley['code']) && $smiley['code'] != '') {
                $replacements[$smiley['code']] = $smiley['full_url'];
            }
        }
        $channel_smileys = $settings['smileys'];
        foreach ($channel_smileys as $index => $smiley) {
            $replacements[':custom_'.$index.':'] = $smiley['full_url'];
            if (isset($smiley['code']) && $smiley['code'] != '') {
                $replacements[$smiley['code']] = $smiley['full_url'];
            }
        }
        return $replacements;
    }


    public function clear($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['moderation']);
        $user = ChatHelper::getCurrentConnectedUser($channel->id);
        ChatMessage::where(['channel_id' => $channel_id])->delete();
        broadcast(new ChatClearEvent($channel_id, $user));
        return [
            'message'=>'chat.messages.cleared',
        ];
    }



    public function getBanStateForMessage($channel_id, $message_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['moderation']);
        $message = ChatMessage::where(['id' => $message_id, 'channel_id' => $channel_id])->first();

        $is_banned = false;
        $can_change_ban_state = false;
        if ($message && $message->user) {
            $is_banned = UserBan::where([
                'user_id' => $message->user->id,
                'channel_id' => $channel->id
            ])->count() > 0;
            $can_change_ban_state = ChatHelper::canChangeBanState($channel, $message->user);
        } elseif ($message && $message->ip_address) {
            $is_banned = IPBan::firstOrNew([
                'ip_address' => $message->ip_address,
                'channel_id' => $channel->id,
            ])->count() > 0;
            $can_change_ban_state = true;
        }
        return ['is_banned' => $is_banned, 'can_change_ban_state' => $can_change_ban_state];
    }

    public function changeBanStateForMessage($channel_id, $message_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['moderation']);
        $message = ChatMessage::where(['id' => $message_id, 'channel_id' => $channel_id])->first();
        $state = !!request()->input('state', true);

        if ($message->user) {
            ChatHelper::changeUserBanState($channel, $message->user, $state);
        } else {
            ChatHelper::changeGuestBanState($channel, null, $message->ip_address, $state);
        }
        return [
            'message' => $state ? 'chat.messages.banned' : 'chat.messages.unbanned'
        ];
    }

    public function changeUserBanState($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['moderation']);

        if (!request()->has('user_id') || !request()->has('state')) {
            return response()->json(['message' => 'chat.errors.no_parameters'], 422);
        }
        $user_to_ban = User::findOrFail(request()->input('user_id'));
        $state = !!request()->input('state', true);
        $reason = request()->input('reason', '');

        if (ChatHelper::canChangeBanState($channel, $user_to_ban)) {
            ChatHelper::changeUserBanState($channel, $user_to_ban, $state, $reason);
            return [
                'message' => $state ? 'chat.messages.user_banned' : 'chat.messages.user_unbanned'
            ];
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function changeGuestBanState($channel_id){
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['moderation']);

        $guest_id = null;
        $ip_address = null;
        if (request()->has('ip_address')) {
            $data = request()->validate([
                'ip_address' => 'required|ip',
            ]);
            $ip_address = $data['ip_address'];
        } elseif (request()->has('guest_id')) {
            $guest = ChatHelper::getGuestData(request()->input('guest_id'));
            if ($guest) {
                $guest_id = request()->input('guest_id');
                $ip_address = $guest['ip_address'];
            }
        }
        if (!$ip_address) {
            return response()->json(['message' => 'chat.errors.no_parameters'], 422);
        }

        $state = !!request()->input('state', true);
        $reason = request()->input('reason', '');

        ChatHelper::changeGuestBanState($channel, $guest_id, $ip_address, $state, $reason);
        return [
            'message' => $state ? 'chat.messages.banned' : 'chat.messages.unbanned'
        ];
    }

    public function changeColor($channel_id) {
        $channel = Channel::findOrFail($channel_id);
        $color = ChatHelper::parseColor(request()->input('color'));

        $user = auth()->user();
        $update = ['color' => $color];
        $connected_user = ChatHelper::getConnectedUserAndUpdate($channel->id, $update);

        if ($user) {
            broadcast(new ChatChangeUserStateEvent($channel, $connected_user, $connected_user, $update));
        } else {
            broadcast(new ChatChangeGuestStateEvent($channel, null, $connected_user, $update));
        }
        return $update;
    }

    public function changeGuestUsername($id){
        $channel = Channel::findOrFail($id);
        $data = request()->validate([
            'username' => 'nullable|min:1,max:50',
        ]);
        $username = $data['username'] ?? ''; // todo: check validity, uniqueness
        $connected_user = ChatHelper::getConnectedUserAndUpdate($channel->id, ['username' => $username]);
        broadcast(new ChatChangeGuestStateEvent($channel, null, $connected_user, [
            'username' => $username
        ]));
        return ['username' => $username];
    }


    public function destroy($id) {
        $message = ChatMessage::findOrFail($id);
        $user = auth()->user();
        PermissionsHelper::check(['moderation'], $message->channel, $message);
        broadcast(new ChatMessageDeletedEvent($message->channel, $user, $message));
        $message->delete();
        return $message;
    }

    public function authorizeGuest($id) {
        $channel = Channel::findOrFail($id);
        $username = request()->input('username');
        return ChatHelper::authorizeGuest($channel, $username);
    }

    public function getChatUsersPublic($id) {
        $channel = Channel::findOrFail($id);
        return ChatHelper::getChatUsers($channel->id);
    }

}
