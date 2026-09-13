<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'               => $this->id,
            'role_id'          => $this->role_id,
            'username'         => $this->username,
            'full_name'        => $this->full_name,
            'domain'           => $this->domain,
            'about'            => $this->about,
            'status_text'      => $this->status_text,
            'avatar'           => $this->avatar,
            'pictures_data'    => $this->pictures_data,
            'is_admin'         => $this->is_admin,
            'last_seen'        => $this->last_seen,
            'last_ip_address'  => $this->last_ip_address,
            'public_key'       => $this->public_key,
            'actor_id'         => $this->actor_id,
            'key_id'           => $this->key_id,
            'inbox_url'        => $this->inbox_url,
            'outbox_url'       => $this->outbox_url,
            'shared_inbox_url' => $this->shared_inbox_url,
            'web_url'          => $this->web_url,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }

}
