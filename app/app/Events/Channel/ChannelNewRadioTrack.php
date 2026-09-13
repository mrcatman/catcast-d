<?php

namespace App\Events\Channel;

use App\Models\Audio;

use App\Models\Channel;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChannelNewRadioTrack implements ShouldBroadcast
{
    use SerializesModels;

    public $track;
   // public $channel;
    public $data;
    private $channel_id;


    public function __construct($channel_id, Audio $track, $data = [])
    {
       // $this->channel = $channel;
        $this->track = $track;
        $this->data = $data;
        $this->channel_id = $channel_id;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('App.Channel.'.$this->channel_id);
    }

    public function broadcastAs()
    {
        return 'channel.new_radio_track';
    }
}
