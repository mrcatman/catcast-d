<?php

namespace App\Traits;

use App\Models\Tag;

trait HasTags {

    public function setTagsAttribute($tags) {
        if (!$this->id) {
            return;
        }
        Tag::where(['entity_type' => self::getEntityType(), 'entity_id' => $this->id])->delete();

        if (!is_iterable($tags)) {
            $tags = [$tags];
        }
        $tags_data = [];
        foreach ($tags as $tag) {
            $tags_data[] = [
                'entity_type' => self::getEntityType(),
                'entity_id' => $this->id,
                'tag' => $tag
            ];
        }
        Tag::insert($tags_data);
    }

    public function getTagsAttribute() {
        $tags = $this->hasMany(Tag::class, 'entity_id', 'id')->where(['entity_type' => self::getEntityType()])->get();
        return $tags->pluck('tag');
    }

}
