<?php

namespace App\Events\Channel;


use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChannelLiveStreamEndedEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $channel;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.Channel.'.$this->channel->id);
    }

    public function broadcastAs()
    {
        return 'channel.live_stream_ended';
    }
}
