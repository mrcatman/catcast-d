<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Comment
 */
class CommentResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'channel_id'  => $this->channel_id,
            'entity_type' => $this->entity_type,
            'entity_id'   => $this->entity_id,
            'title'       => $this->title,
            'text'        => $this->text,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
        ];
    }

}
