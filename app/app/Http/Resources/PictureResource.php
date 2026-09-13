<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Picture
 */
class PictureResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'channel_id'   => $this->channel_id,
            'domain'       => $this->domain,
            'relative_url' => $this->relative_url,
            'full_url'     => $this->full_url,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'deleted_at'   => $this->deleted_at,
        ];
    }

}
