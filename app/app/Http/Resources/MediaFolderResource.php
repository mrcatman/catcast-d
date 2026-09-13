<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\MediaFolder
 */
class MediaFolderResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'                   => $this->id,
            'channel_id'           => $this->channel_id,
            'user_id'              => $this->user_id,
            'parent_id'            => $this->parent_id,
            'title'                => $this->title,
            'description'          => $this->description,
            'is_public'            => $this->is_public,
            'connected_project_id' => $this->connected_project_id,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
            'deleted_at'           => $this->deleted_at,
        ];
    }

}
