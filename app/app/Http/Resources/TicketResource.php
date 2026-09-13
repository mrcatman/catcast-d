<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ticket
 */
class TicketResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'ip'                   => $this->ip,
            'contacts'             => $this->contacts,
            'category'             => $this->category,
            'title'                => $this->title,
            'connected_page'       => $this->connected_page,
            'connected_page_title' => $this->connected_page_title,
            'is_important'         => $this->is_important,
            'is_closed'            => $this->is_closed,
            'status'               => $this->status,
            'unread_messages'      => $this->unread_messages,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }

}
