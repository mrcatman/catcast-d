<?php

namespace App\Helpers;

use App\Models\Broadcast;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class FiltersHelper {

    /**
     * Apply common filtering (search + tags + filter)
     * @param Builder | Relation $list Query builder object
     * @param string $model_class Model for which filters are applied
     * @return Builder
     */
    public static function applyFromRequest(Builder | Relation $list, string $model_class) {
        if (request()->filled('search')) {
            $list = $list->search(request()->input('search'));
        }
        if (request()->filled('type')) {
            $list = $list->ofSelectedType(request()->input('type'));
        }
        $filter = request()->input('show');
        if (!empty($filter)) {
            $filter_camel_case = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $filter))));
            $method_name = 'filter' . $filter_camel_case;
            $full_method_name = 'scopeFilter' . $filter_camel_case;
            if (method_exists($model_class, $full_method_name)) {
                $list = $list->$method_name();
            }
        } else {
            $list->orderBy('created_at', 'desc');
        }

        if (request()->filled('tags')) {
            $tags = explode(',', request()->input('tags'));
            foreach ($tags as &$tag) {
                $tag = trim($tag);
            }
            $tag_ids = Tag::where(['entity_type'=> $model_class::getEntityType()])->whereIn('tag', $tags)->pluck('entity_id');
            $list = $list->whereIn('id', $tag_ids);
        }
        return $list->paginate(request()->input('count', 16));
    }

}
