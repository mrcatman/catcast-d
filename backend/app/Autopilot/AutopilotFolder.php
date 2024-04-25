<?php

namespace App\Autopilot;
use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Database\Eloquent\Model;

class AutopilotFolder extends Model {

    public $table = "autopilot_folders";

    protected $fillable = ['index', 'title', 'limit_length', 'cycled', 'cycled_till', 'description', 'additional_data', 'show_contents', 'playback_type', 'can_subscribe', 'can_view', 'video_folder_id'];

    protected $appends = ['length','readable_length','readable_time','is_folder', 'time'];

    protected $with = ['items'];

    protected $time = null;

    public function setTime($time) {
        $this->time = $time;
    }

    public function getTimeAttribute() {
        return $this->time;
    }

    public function getIsFolderAttribute() {
        return true;
    }

    public function getReadableTimeAttribute() {
        return date('d.m.Y H:i', $this->time);
    }

    public function getLengthAttribute() {
        return $this->items->sum('length');
    }

    public function getReadableLengthAttribute() {
        $seconds = $this->length;
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%H:%I:%S');
    }

    public function getIsCurrentAttribute() {
        return $this->time <= time() && time() <= ($this->time + $this->length);
    }

    public function items() {
        return $this->hasMany(AutopilotItem::class, 'folder_id', 'id');
    }


    public function getConnectedItems($playlist_id) {
        if (!$this->video_folder_id) {
            $folder = MediaFolder::where(['channel_id' => $this->playlist->channel_id, 'title' => $this->title])->first();
            if ($folder) {
                $this->video_folder_id = $folder->id;
            }
        }
        if (!$this->video_folder_id) {
            return [];
        }
        $videos = Media::where(['folder_id' => $this->video_folder_id])->get();
        $items = [];
        $i = 0;
        foreach ($videos as $video) {
            $item = new AutopilotItem([
                'inner_id' => "folder_".$this->id."_".$i,
                'playlist_id' => $playlist_id,
                'video_id' => $video->id,
                'folder_id' => $this->id,
                'title' => $video->title,
                'index' => $i,
                'length' => $video->length,
                'can_subscribe' => false,
                'can_view' => false,
                'description' => $video->description
            ]);
            $items[] = $item;
            $i++;
        }
        return $items;
    }

    public function playlist() {
        return $this->belongsTo(AutopilotPlaylist::class);
    }

    public function getPictureAttribute() {
        if ($this->video) {
            return $this->video->thumbnail;
        }
        return null;
    }

    public function getCurrentVideoServer() {
        return $this->items[0]->getCurrentVideoServer();
    }

}
