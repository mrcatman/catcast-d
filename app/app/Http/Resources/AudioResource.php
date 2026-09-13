<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Audio
 */
class AudioResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'            => $this->id,
            'channel_id'    => $this->channel_id,
            'user_id'       => $this->user_id,
            'folder_id'     => $this->folder_id,
            'filename'      => $this->filename,
            'folder'        => $this->folder,
            'folders'       => $this->folders,
            'author'        => $this->author,
            'title'         => $this->title,
            'album'         => $this->album,
            'description'   => $this->description,
            'domain'        => $this->domain,
            'url'           => $this->url,
            'file_size'     => $this->file_size,
            'length'        => $this->length,
            'is_public'     => $this->is_public,
            'upload_status' => $this->upload_status,
            'is_recording'  => $this->is_recording,
            'views'         => $this->views,
            'object_id'     => $this->object_id,
            'waveform_data' => $this->waveform_data,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,
        ];
    }

}
