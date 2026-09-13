<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Like
 */
class LikeResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'entity_type' => $this->entity_type,
            'entity_id'   => $this->entity_id,
            'weight'      => $this->weight,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }

}
