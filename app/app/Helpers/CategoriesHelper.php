<?php

namespace App\Helpers;

use App\Models\Broadcast;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class CategoriesHelper
{

    /**
     * Get category ID from request or create a new one if the name is provided
     * @return int? $category_id
     */
    public static function getIdFromRequest()
    {
        $category = null;
        if (request()->filled('category_id')) {
            $category = Category::findOrFail(request()->input('category_id'));
        } elseif (request()->filled('category.id')) {
            $category = Category::findOrFail(request()->input('category.id'));
        } elseif (request()->filled('category.name')) {
            $category = Category::firstOrNew([ // todo: add to config whether creating new categories is allowed
                'name' => request()->input('category.name')
            ]);
            $category->save();
        }
        return $category ? $category->id : null;
    }

}
