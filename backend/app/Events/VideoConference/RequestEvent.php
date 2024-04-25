<?php

namespace App\Events\VideoConference;

use App\Models\VideoConference;


use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequestEvent implements ShouldBroadcast
{
    use SerializesModels, InteractsWithSockets;

    public $conference;
    public $user_id;
    public function __construct(VideoConference $conference, $user_id)
    {
        $this->conference = $conference;
        $this->user_id = $user_id;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.VideoConference.'.$this->conference->uuid);
    }

    public function broadcastAs()
    {
        return 'videoconference.request';
    }
}
