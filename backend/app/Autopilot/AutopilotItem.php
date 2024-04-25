<?php

namespace App\Autopilot;
use App\Traits\HasTags;
use App\Models\Media;
use Illuminate\Database\Eloquent\Model;

class AutopilotItem extends Model {

    use HasTags;

    public $table = "autopilot_items";

    protected $fillable = ['inner_id', 'video_id', 'index', 'title', 'length', 'description', 'additional_data', 'folder_id'];

    protected $appends = ['time', 'readable_time', 'readable_length', 'is_current', 'picture'];

    protected $hidden = ['video'];

    protected $time = null;

    protected $entity_type = 'autopilot_items';

    public function setTime($time) {
        $this->time = $time;
    }

    public function getTimeAttribute() {
        return $this->time;
    }

    public function getReadableTimeAttribute() {
        return date('d.m.Y H:i', $this->time);
    }

    public function getReadableLengthAttribute() {
        $seconds = $this->length;
        if (!$seconds) {
            $seconds = 0;
        }
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%H:%I:%S');
    }

    public function getLengthAttribute() {
        if (!$this->attributes['length']) {
            return 0;
        }
        return $this->attributes['length'];
    }

    public function getIsCurrentAttribute() {
        return $this->time <= time() && time() <= ($this->time + $this->length);
    }


    public function video() {
        return $this->belongsTo(Media::class, 'video_id', 'id');
    }

    public function playlist() {
        return $this->belongsTo(AutopilotPlaylist::class);
    }

    public function getPictureAttribute() {
        if (isset($GLOBALS['video_data_'.$this->video_id])) {
            return $GLOBALS['video_data_'.$this->video_id]->thumbnail;
        }
        if ($this->video) {
            return $this->video->thumbnail;
        }
        return null;
    }

    public function getCurrentVideoServer() {
        if (isset($GLOBALS['video_data_'.$this->video_id])) {
            return $GLOBALS['video_data_'.$this->video_id]->server;
        }
        if ($this->video) {
            return $this->video->server;
        }
        if ($this->playlist->channel->id === 33418) {
            return "karapuztv.fenixplustv.xyz";
        }
        return null;
    }
}
