<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Autopilot\AutopilotTempItem
 */
class AutopilotTempItemResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'           => $this->id,
            'channel_id'   => $this->channel_id,
            'playlist_id'  => $this->playlist_id,
            'folder_id'    => $this->folder_id,
            'item_id'      => $this->item_id,
            'repeat_count' => $this->repeat_count,
            'clip_index'   => $this->clip_index,
            'time_start'   => $this->time_start,
            'time_end'     => $this->time_end,
            'length'       => $this->length,
            'length_total' => $this->length_total,
            'title'        => $this->title,
            'data'         => $this->data,
        ];
    }

}
