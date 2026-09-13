<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ChatMessage
 */
class ChatMessageResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'         => $this->id,
            'channel_id' => $this->channel_id,
            'user_id'    => $this->user_id,
            'username'   => $this->username,
            'text'       => $this->text,
            'color'      => $this->color,
            'reply_to'   => $this->reply_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
