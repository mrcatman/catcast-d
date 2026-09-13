<?php

namespace App\Events\Chat;

use App\Models\User;
use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatChangeStateEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $state;
    public $channel;

    public function __construct(Channel $channel, User $user = null, $state)
    {
        $this->channel = $channel;
        $this->user = $user;
        $this->state = $state;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.Chat.'.$this->channel->id);
    }

    public function broadcastAs()
    {
        return 'chat.state_changed';
    }
}
