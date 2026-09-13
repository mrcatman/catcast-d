<?php

namespace App\Events\Mail;

use App\Models\Message;
use App\Models\User;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MailMessage implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $message;

    public function __construct(User $user, Message $message)
    {

        $this->user = $user;
        $this->message = $message;
        $message->load('user:id,username,last_seen');
        $message->user_id = $user->id;
     }

    public function broadcastOn()
    {
        return new PrivateChannel('App.User.'.$this->user->id);
    }

    public function broadcastAs()
    {
        return 'mail.message';
    }
}
