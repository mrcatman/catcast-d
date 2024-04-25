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

    public function onPublish() {
        try {
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
        } catch (\Exception $e) {
            file_put_contents(public_path("logs/tv_auth.txt"), json_encode([$e->getTraceAsString(), $e->getMessage()]));
        }
        return '';
//        $need_record = false;
//        if ($channel->additional_settings['records']['record_all']) {
//            $need_record = true;
//        }
//        $announce = Announce::where(['channelid' => $channel->id, 'need_to_record' => true])->where('created_at', '<=', time() + 600)->whereRaw('addtime + (length * 60) >= ' . time())->orderBy('created_at', 'desc')->first();
//        if ($announce) {
//            $need_record = true;
//        }
//        file_put_contents(public_path("logs/tv_auth_record.txt"), json_encode(['need_record' => $need_record]));
//        if ($need_record) {
//            try {
//                StartRecording::dispatch($channel, $online_instance, $announce)->delay(now()->addSeconds(10));
//            } catch (\Exception $e) {
//
//            }
//        }


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
