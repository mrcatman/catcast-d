<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Models\Channel;
use App\Models\User;

use App\Helpers\ChatHelper;
use App\Helpers\PermissionsHelper;

Route::any('/broadcasting/auth', function (Illuminate\Http\Request $request) {
    $user = null;
    if (!$request->user()) {
        $channel_name = explode('presence-App.Chat.', request()->input('channel_name'));
        if (count($channel_name) === 2) {
            Channel::findOrFail($channel_name[1]);
            $guest_id = request()->header('X-Guest-Id');
            $data = Cache::get('chat.guest.'.$guest_id);
            if (!$data) {
                return abort(403);
            }

            $user = User::factory()->make([
                'id' => -1,
                'guest_id' => $guest_id,
                'username' => $data['username'],
                'is_guest' => true,
            ]);
        }

    } else {
        $user = $request->user();
    }
    if ($user) {
        $user->color = ChatHelper::parseColor(request()->header('X-Chat-Color'));
        Auth::login($user);
    }

    return Broadcast::auth($request);
})->middleware(\App\Http\Middleware\SetUserOnRequest::class);

Broadcast::channel('App.User.{id}', function ($user, $user_id) {
    return (int)$user->id === (int)$user_id;
});

Broadcast::channel('App.Dashboard.{id}', function ($user, $channel_id) {
    $channel = Channel::findOrFail($channel_id);
    return PermissionsHelper::hasAny($channel, $user);
});


Broadcast::channel('App.Channel.{id}', function ($user, $id) {
    $channel = Channel::find($id);
    if (!$channel) {
        return false;
    }
    if ($user) {
        return [
            'id' => $user->id,
        ];
    } else {
        return false;
    }
});

Broadcast::channel('App.Chat.{id}', function($user_data, $channel_id) {
    return ChatHelper::authenticate($user_data, $channel_id);
});
