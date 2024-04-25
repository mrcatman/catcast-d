<?php
namespace App\Autopilot;
use App\Models\Channel;
use App\Models\Media;

class AutopilotItemOld implements \Illuminate\Contracts\Support\Arrayable {
    protected $title;
    protected $length;
    protected $path;
    protected $video;
    protected $id;
    protected $folder;
    protected $channel_id;
    protected $playlist_data;
    public $time;
    public function __construct($data, $channel_id, $playlist = null) {
        if (isset($data->title)) {
            $this->title = $data->title;
        }
        $this->id = (int)$data->id;
        if (isset($data->url)) {
            $this->path = $data->url;
        }
        $this->length = (int)$data->length;
        if (isset($data->folder)) {
            $this->folder = $data->folder;
        }
        $this->channel_id = (int)$channel_id;
        if ($playlist) {
            $this->playlist_data = $playlist;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getLength() {
        return $this->length;
    }

    public function getChannelId() {
        return $this->channel_id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getVideoInstance() {
        if ($this->video) {
            return $this->video;
        }
        $video = Media::find($this->id);
        $this->video = $video;
        return $video;
    }

    public function getServer() {
        if (!$this->video) {
            $this->getVideoInstance();
        }
        if ($this->channel_id === 33418) {
            return "karapuztv.fenixplustv.xyz";
        }
        if ($this->video) {
            return $this->video->server;
        }
        return null;
    }

    public function getPicture() {
        if (!$this->video) {
            $this->getVideoInstance();
        }
        if ($this->video) {
            return $this->video->thumbnail;
        }
        return null;
    }

    public function getDescription() {
        if (!$this->video) {
            $this->getVideoInstance();
        }
        if ($this->video) {
            return $this->video->description;
        }
        return null;
    }

    public function setTime($time) {
        $this->time = $time;
    }

    public function getTime() {
        return $this->time;
    }

    public function getReadableTime() {
        return date('d.m.Y H:i', $this->time);
    }

    public function getReadableLength() {
        $seconds = $this->length;
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%H:%I:%S');
    }

    public function getChannel() {
        return Channel::find($this->channel_id);
    }

    public function canPlay() {
        if (!$this->video) {
            $this->getVideoInstance();
        }
        return (bool)$this->video;
    }

    public function getPlaylistId() {
        if ($this->playlist_data) {
            return $this->playlist_data->getPlaylistId();
        }
        return null;
    }

    public function toArray() {
        return [
            'title' => $this->title,
            'length' => $this->length,
            'time' => $this->getTime(),
            'picture' => $this->getPicture(),
            'description' => $this->getDescription(),
            'id' => $this->id,
            'readable_length' => $this->getReadableLength(),
            'readable_time' => $this->getReadableTime(),
            'can_play' => $this->canPlay(),
            'playlist_id' => $this->getPlaylistId(),
        ];
    }
}
