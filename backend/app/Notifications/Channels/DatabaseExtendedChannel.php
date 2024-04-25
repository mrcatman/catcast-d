<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel;

class DatabaseExtendedChannel extends DatabaseChannel {

    protected function buildPayload($notifiable, $notification) {
        $entity = $notification->getEntity();
        return  [
            'id' => $notification->id,
            'type' => $notification->getEventTypeId(),
            'data' => $this->getData($notifiable, $notification),
            'read_at' => null,
            'entity_type' => $entity::getEntityType(),
            'entity_id' => $entity->id
        ];
    }
}
