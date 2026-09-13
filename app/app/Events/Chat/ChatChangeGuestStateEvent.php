<?php

namespace App\Events\Chat;

use App\Models\User;
use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatChangeGuestStateEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $guest;
    public $state;
    public $channel;

    public function __construct(Channel $channel, $user, $guest, $state) {
        $this->channel = $channel;
        $this->user = $user;
        $this->guest = $guest;
        $this->state = $state;
    }

    public function broadcastOn() {
        return new PresenceChannel('App.Chat.'.$this->channel->id);
    }

    public function broadcastAs() {
        var_dump($this->guest);
        return 'chat.guest_state_changed';
    }
}
