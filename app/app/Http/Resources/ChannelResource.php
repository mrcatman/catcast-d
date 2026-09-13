<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Channel
 */
class ChannelResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'name'              => $this->name,
            'description'       => $this->description,
            'shortname'         => $this->shortname,
            'domain'            => $this->domain,
            'channel_type'      => $this->channel_type,
            'type_name'         => $this->type_name,
            'is_radio'          => $this->is_radio,
            'colors_scheme'     => $this->colors_scheme,
            'broadcast_id'      => $this->broadcast_id,
            'active_broadcast'  => $this->active_broadcast,
            'views'             => $this->views,
            'likes_count'       => $this->likes_count,
            'links'             => $this->links,
            'tags'              => $this->tags,
            'pictures_data'     => $this->pictures_data,
            'logo'              => $this->logo,
            'banner'            => $this->banner,
            'background'        => $this->background,
            'player_background' => $this->player_background,
            'local_url'         => $this->local_url,
            'last_watched_at'   => $this->last_watched_at,
            'last_online_at'    => $this->last_online_at,
            'blocked_at'        => $this->blocked_at,
            'block_reason'      => $this->block_reason,
            'public_key'        => $this->public_key,
            'actor_id'          => $this->actor_id,
            'key_id'            => $this->key_id,
            'inbox_url'         => $this->inbox_url,
            'outbox_url'        => $this->outbox_url,
            'shared_inbox_url'  => $this->shared_inbox_url,
            'web_url'           => $this->web_url,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
        ];
    }

}
