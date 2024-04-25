<?php

namespace App\Http\Controllers;


use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use App\Models\StreamKey;

class ChannelStreamController extends Controller{


    public function getServersList() { // todo: move to other controller, rename
        $url = config('urls.broadcast.rtmp_url');
        $app = config('urls.broadcast.rtmp_app_name');
        return [
            [
                'url' => $url,
                'app' => $app,
                'full_address' => $url.'/'.$app
            ]
        ];
    }

    public function getKey($id){
        $channel = PermissionsHelper::getChannelIfAllowed($id, ['live_broadcast']);

        $user = auth()->user();
        $generate_new_key = !!request()->input('generate_new_key', false);
        $key = StreamKey::firstOrNew(['channel_id' => $channel->id, 'user_id' => $user->id]);
        if (!$key->exists || $generate_new_key) {
            $key->key = $key->generateKey($user->id, $channel->id);
        }
        $key->save();
        return $key;
    }



}
