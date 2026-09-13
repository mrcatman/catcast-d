<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Autopilot\AutopilotPlaylist
 */
class AutopilotPlaylistResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'            => $this->id,
            'channel_id'    => $this->channel_id,
            'user_id'       => $this->user_id,
            'data'          => $this->data,
            'extended_data' => $this->extended_data,
            'items'         => $this->items,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,
        ];
    }

}
