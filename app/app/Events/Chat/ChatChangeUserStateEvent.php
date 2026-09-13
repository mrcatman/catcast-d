<?php

namespace App\Events\Chat;

use App\Models\User;
use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatChangeUserStateEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $user_from;
    public $user_to;
    public $state;
    public $channel;

    public function __construct(Channel $channel, $user_from, $user_to, $state)
    {
        $this->channel = $channel;
        $this->user_from = $user_from;
        $this->user_to = $user_to;
        $this->state = $state;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.Chat.'.$this->channel->id);
    }

    public function broadcastAs()
    {
        return 'chat.user_state_changed';
    }
}
