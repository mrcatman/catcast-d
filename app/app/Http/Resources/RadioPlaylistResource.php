<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RadioPlaylist
 */
class RadioPlaylistResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'                    => $this->id,
            'channel_id'            => $this->channel_id,
            'created_by'            => $this->created_by,
            'title'                 => $this->title,
            'description'           => $this->description,
            'cover'                 => $this->cover,
            'playback_weight'       => $this->playback_weight,
            'playback_type'         => $this->playback_type,
            'playback_order'        => $this->playback_order,
            'playback_data'         => $this->playback_data,
            'last_play_index'       => $this->last_play_index,
            'last_play_track_index' => $this->last_play_track_index,
            'last_play_time'        => $this->last_play_time,
            'last_play_date'        => $this->last_play_date,
            'already_played_tracks' => $this->already_played_tracks,
            'can_accept_requests'   => $this->can_accept_requests,
            'is_visible'            => $this->is_visible,
            'is_special'            => $this->is_special,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'deleted_at'            => $this->deleted_at,
        ];
    }

}
