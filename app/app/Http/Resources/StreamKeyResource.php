<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\StreamKey
 */
class StreamKeyResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'         => $this->id,
            'channel_id' => $this->channel_id,
            'user_id'    => $this->user_id,
            'key'        => $this->key,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
