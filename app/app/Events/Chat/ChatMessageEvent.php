<?php

namespace App\Events\Chat;

use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Channel;

use Illuminate\Broadcasting\Channel as BroadcastChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessageEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $message;
    public $channel;

    public function __construct(Channel $channel, User $user = null, ChatMessage $message)
    {
        $this->channel = $channel;
        $this->user = $user;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.Chat.'.$this->channel->id);
    }

    public function broadcastAs()
    {
        return 'chat.message';
    }
}
