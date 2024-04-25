<?php

namespace App\Events\Channel;

use App\Models\Broadcast;

use App\Models\Channel;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;


class ChannelBroadcastStateChangedEvent implements ShouldBroadcast {

    use SerializesModels;

    public $channel;
    public $broadcast;

    public function __construct(Channel $channel, Broadcast $broadcast) {
        $this->channel = $channel;
        $this->broadcast = $broadcast;
    }

    public function broadcastOn() {
        return new PresenceChannel('App.Channel.'.$this->channel->id);
    }

    public function broadcastAs() {
        return 'channel.broadcast_state_changed';
    }
}
