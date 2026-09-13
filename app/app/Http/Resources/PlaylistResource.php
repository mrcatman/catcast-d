<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Playlist
 */
class PlaylistResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'                  => $this->id,
            'uuid'                => $this->uuid,
            'channel_id'          => $this->channel_id,
            'user_id'             => $this->user_id,
            'name'                => $this->name,
            'description'         => $this->description,
            'links'               => $this->links,
            'colors_scheme'       => $this->colors_scheme,
            'use_custom_design'   => $this->use_custom_design,
            'privacy_status'      => $this->privacy_status,
            'privacy_status_name' => $this->privacy_status_name,
            'views'               => $this->views,
            'object_id'           => $this->object_id,
            'tags'                => $this->tags,
            'pictures_data'       => $this->pictures_data,
            'logo'                => $this->logo,
            'banner'              => $this->banner,
            'background'          => $this->background,
            'player_background'   => $this->player_background,
            'local_url'           => $this->local_url,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }

}
