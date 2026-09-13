<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\IPBan
 */
class IPBanResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'          => $this->id,
            'channel_id'  => $this->channel_id,
            'ip_address'  => $this->ip_address,
            'reason'      => $this->reason,
            'banned_by'   => $this->banned_by,
            'banned_till' => $this->banned_till,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }

}
