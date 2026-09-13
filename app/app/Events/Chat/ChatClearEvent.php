<?php

namespace App\Events\Chat;

use App\Models\User;
use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatClearEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $channel_id;

    public function __construct($channel_id, $user)
    {
        $this->channel_id = $channel_id;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.Chat.'.$this->channel_id);
    }

    public function broadcastAs()
    {
        return 'chat.clear';
    }
}
