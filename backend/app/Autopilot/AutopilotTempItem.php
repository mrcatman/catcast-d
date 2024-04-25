<?php

namespace App\Autopilot;
use Illuminate\Database\Eloquent\Model;

class AutopilotTempItem extends Model
{
    public $table = "autopilot_temp_items";
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'data' => 'array'
    ];

    public function item() {
        return $this->belongsTo(AutopilotItem::class, 'item_id', 'id');
    }

    public function getItem() {
        $item = $this->item;
        if ($item) {
            return $item;
        }
        if (substr( $this->item_id, 0, 6 ) === "folder") {
            $folder_data = explode("_", $this->item_id);
            $folder = AutopilotFolder::find($folder_data[1]);
            if ($folder) {
                $items = $folder->getConnectedItems($this->playlist_id);
                if (isset($items[$folder_data[2]])) {
                    return $items[$folder_data[2]];
                }
            }
        }
    }
}