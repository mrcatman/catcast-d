<?php

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MediaConvertProgressEvent implements ShouldBroadcast {
    use SerializesModels;

    public $media;
    public $percent;

    public function __construct(Media $media, $percent) {
        $this->media = $media;
        $this->percent = $percent;
    }

    public function broadcastOn() {
        return new PrivateChannel('App.User.'.$this->media->user->id);
    }

    public function broadcastAs() {
        return 'media.convert_progress';
    }
}
