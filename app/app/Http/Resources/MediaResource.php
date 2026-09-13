<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Media
 */
class MediaResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'                  => $this->id,
            'uuid'                => $this->uuid,
            'channel_id'          => $this->channel_id,
            'user_id'             => $this->user_id,
            'broadcast_id'        => $this->broadcast_id,
            'category_id'         => $this->category_id,
            'folder_id'           => $this->folder_id,
            'server_id'           => $this->server_id,
            'object_id'           => $this->object_id,
            'thumbnail_id'        => $this->thumbnail_id,
            'title'               => $this->title,
            'description'         => $this->description,
            'media_type'          => $this->media_type,
            'type_name'           => $this->type_name,
            'source_type'         => $this->source_type,
            'source_type_name'    => $this->source_type_name,
            'privacy_status'      => $this->privacy_status,
            'privacy_status_name' => $this->privacy_status_name,
            'duration'            => $this->duration,
            'views'               => $this->views,
            'likes_count'         => $this->likes_count,
            'tags'                => $this->tags,
            'upload_ready'        => $this->upload_ready,
            'local_url'           => $this->local_url,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }

}
