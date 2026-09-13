<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\UserChannelPermissions
 */
class UserChannelPermissionsResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'               => $this->id,
            'channel_id'       => $this->channel_id,
            'user_id'          => $this->user_id,
            'added_by_user_id' => $this->added_by_user_id,
            'permissions'      => $this->permissions,
            'position'         => $this->position,
            'hidden'           => $this->hidden,
            'confirmed'        => $this->confirmed,
            'left_at'          => $this->left_at,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'deleted_at'       => $this->deleted_at,
        ];
    }

}
