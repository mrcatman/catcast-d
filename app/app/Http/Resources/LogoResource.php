<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Logo
 */
class LogoResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'         => $this->id,
            'channel_id' => $this->channel_id,
            'user_id'    => $this->user_id,
            'picture_id' => $this->picture_id,
            'is_active'  => $this->is_active,
            'index'      => $this->index,
            'position'   => $this->position,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
