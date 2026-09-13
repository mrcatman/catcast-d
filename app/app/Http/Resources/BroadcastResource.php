<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Broadcast
 */
class BroadcastResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'            => $this->id,
            'channel_id'    => $this->channel_id,
            'user_id'       => $this->user_id,
            'server'        => $this->server,
            'server_id'     => $this->server_id,
            'object_id'     => $this->object_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'language'      => $this->language,
            'watch_url'     => $this->watch_url,
            'playback_url'  => $this->playback_url,
            'thumbnail_url' => $this->thumbnail_url,
            'is_online'     => $this->is_online,
            'tags'          => $this->tags,
            'views'         => $this->views,
            'viewers'       => $this->viewers,
            'started_at'    => $this->started_at,
            'ended_at'      => $this->ended_at,
            'will_start_at' => $this->will_start_at,
            'will_end_at'   => $this->will_end_at,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }

}
