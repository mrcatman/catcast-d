<?php

namespace App\Events\Channel;

use App\Donate;
use App\Models\User;
use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChannelNewDonateEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $donate;
    public $channel;

    public function __construct(Channel $channel, Donate $donate, User $user)
    {
        $this->channel = $channel;
        $this->user = $user;
        $this->donate = $donate;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.Channel.'.$this->channel->id);
    }

    public function broadcastAs()
    {
        return 'channel.new_donate';
    }
}
