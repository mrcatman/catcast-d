<?php

namespace App\Events\Chat;

use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessageDeletedEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $channel;
    public $message;

    public function __construct(Channel $channel, User $user = null, ChatMessage $message = null)
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
        return 'chat.message_deleted';
    }
}
