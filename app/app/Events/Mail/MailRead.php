<?php

namespace App\Events\Mail;

use App\Models\Message;
use App\Models\User;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MailRead implements ShouldBroadcast
{
    use SerializesModels;

    public $dialog_id;
    private $user;
    public function __construct($user, $dialog_id)
    {
        $this->user = $user;
        $this->dialog_id = $dialog_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.User.'.$this->user->id);
    }

    public function broadcastAs()
    {
        return 'mail.read';
    }
}
