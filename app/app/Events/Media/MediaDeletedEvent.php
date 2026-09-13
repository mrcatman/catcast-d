<?php

namespace App\Events\Media;

use App\Models\Media;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MediaDeletedEvent implements ShouldBroadcast {
    use SerializesModels;

    public $media;

    public function __construct($media) {
        $this->media = $media;
    }

    public function broadcastOn() {
        return new PrivateChannel('App.Dashboard.'.$this->media['channel_id']);
    }

    public function broadcastAs() {
        return 'media.deleted';
    }
}
