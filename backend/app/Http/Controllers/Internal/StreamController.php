<?php

namespace App\Http\Controllers\Internal;

use App\Events\Channel\ChannelBroadcastStateChangedEvent;
use App\Http\Controllers\Controller;
use App\Helpers\CommonResponses;

use App\Models\Broadcast;
use App\Models\Channel;
use App\Models\StreamKey;
use App\Notifications\Types\NewBroadcast;
use Carbon\Carbon;

class StreamController extends Controller {

    public function onPublish()
    {

        $key = request()->input('key');
        $id = request()->input('name');
        $channel = Channel::find($id);
        if (!$channel || $channel->is_banned) {
            return CommonResponses::unauthorized();
        };
        $stream_key = StreamKey::where(['key' => $key, 'channel_id' => $channel->id])->first();
        if (!$stream_key || !$stream_key->user) {
            return CommonResponses::unauthorized();
        };

        $now = Carbon::now();
        $restart_time_limit = $now->clone()->subMinutes(5);

        $broadcast = Broadcast::where(['channel_id' => $stream_key->channel_id])->where(function ($q) use ($restart_time_limit) {
            $q->whereDate('ended_at', '>=', $restart_time_limit); // not ended or ended <5 minutes ago
            $q->orWhereNull('ended_at');
        })->first();

        if (!$broadcast) {
            $broadcast = Broadcast::whereDate('will_start_at', '>=', $now)->whereDate('will_end_at', '<=', $now)->first(); // planned broadcast
        }

        if (!$broadcast) {
            $default_metadata = $channel->additional_settings['default_broadcast_metadata'];
            $broadcast = new Broadcast([
                'channel_id' => $channel->id,
                'title' => $default_metadata['title'],
                'description' => $default_metadata['description'],
                'category_id' => $default_metadata['category_id']
            ]);
        }

        if (!$broadcast->started_at) {
            $broadcast->started_at = $now;
        }
        $broadcast->user_id = $stream_key->user_id;
        $broadcast->ended_at = null;
        $broadcast->save();

        event(new ChannelBroadcastStateChangedEvent($channel, $broadcast));

        // if ($channel->last_online_at && $channel->last_online_at->lt(Carbon::now()->subMinutes(5))) {
        (new NewBroadcast($broadcast))->sendToChannelSubscribers($channel);
        // }

        $channel->last_online_at = Carbon::now();
        $channel->save();

        return '';
    }

    public function onPublishDone() {
        $key = request()->input('key');
        $stream_key = StreamKey::where(['key' => $key])->first();

        if (!$stream_key || !$stream_key->channel) {
            return;
        }

        $channel = $stream_key->channel;
        $broadcast = Broadcast::where([
            'channel_id' => $stream_key->channel_id,
            'user_id' => $stream_key->user_id,
            'ended_at' => null
        ])->first();
        if (!$broadcast) {
            return;
        }
        $broadcast->ended_at = Carbon::now();
        $broadcast->save();

        event(new ChannelBroadcastStateChangedEvent($channel, $broadcast));
        return '';
    }


}
