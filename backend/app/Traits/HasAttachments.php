<?php

namespace App\Traits;

use App\Models\Attachment;

trait HasAttachments {

    public function attachments() {
        return $this->hasMany(Attachment::class,'entity_id')->where(['entity_type'=>$this->attachments_entity_type]);
    }

    public function updateAttachments($items) {
        $existing_attachments_ids = $this->attachments->pluck('id');
        $attachments = collect($items)->map(function($attachment) {
            if (isset($attachment['id'])) {
                $attachment_item = Attachment::find($attachment['id']);
            } else {
                $attachment_item = new Attachment();
            }
            $attachment_item->attachment_type = $attachment['attachment_type'];
            $attachment_item->attachment_id = $attachment['attachment_id'];
            $attachment_item->entity_type = $this->attachments_entity_type;
            $attachment_item->entity_id = $this->id;
            return $attachment_item;
        });
        $new_attachments_ids = $attachments->pluck('id');
        $this->attachments()->saveMany($attachments);
        foreach ($existing_attachments_ids as $attachment_id) {
            if (!$new_attachments_ids->contains($attachment_id)) {
                Attachment::destroy($attachment_id);
            }
        }
    }


}
