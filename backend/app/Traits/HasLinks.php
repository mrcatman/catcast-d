<?php

namespace App\Traits;

use App\Models\EntityLink;
use App\Models\Tag;

trait HasLinks {

    public function setLinksAttribute($links) {
        if (!$this->id) {
            return;
        }
        EntityLink::where(['entity_type' => self::getEntityType(), 'entity_id' => $this->id])->delete();
        $links_data = [];
        foreach ($links as $link) {
            $links_data[] = [
                'entity_type' => self::getEntityType(),
                'entity_id' => $this->id,
                'url' => $link['url'],
                'title' => $link['title']
            ];
        }
        EntityLink::insert($links_data);
    }

    public function getLinksAttribute() {
        return $this->hasMany(EntityLink::class, 'entity_id', 'id')->where(['entity_type' => self::getEntityType()])->get();
    }

}
