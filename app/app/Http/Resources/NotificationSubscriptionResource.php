<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\NotificationSubscription
 */
class NotificationSubscriptionResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'entity_type'       => $this->entity_type,
            'entity_id'         => $this->entity_id,
            'event_type'        => $this->event_type,
            'subscription_data' => $this->subscription_data,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }

}
