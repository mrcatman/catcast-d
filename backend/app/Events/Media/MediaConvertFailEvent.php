<?php

namespace App\Events\Media;

use App\Models\Media;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MediaConvertFailEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $media;
    public $error;

    public function __construct(Media $media, $error) {
        $this->media = $media;
        $this->error = $error;
    }

    public function broadcastOn() {
        return new PrivateChannel('App.User.'.$this->media->user->id);
    }

    public function broadcastAs() {
        return 'media.convert_fail';
    }
}
