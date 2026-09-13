<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\TicketMessage
 */
class TicketMessageResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'         => $this->id,
            'ticket_id'  => $this->ticket_id,
            'user_id'    => $this->user_id,
            'text'       => $this->text,
            'is_answer'  => $this->is_answer,
            'is_read'    => $this->is_read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
