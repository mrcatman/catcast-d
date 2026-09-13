<?php

namespace App\Autopilot;
use App\Http\Resources\AutopilotTempItemResource;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Model;

// Named explicitly because the resource guesser only works for models under an
// App\Models namespace, and this one lives in App\Autopilot.
#[UseResource(AutopilotTempItemResource::class)]
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