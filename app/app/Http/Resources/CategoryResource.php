<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Category
 */
class CategoryResource extends JsonResource {

    public function toArray(Request $request) {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'url'           => $this->url,
            'is_game'       => $this->is_game,
            'category_id'   => $this->category_id,
            'pictures_data' => $this->pictures_data,
            'picture'       => $this->picture,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }

}
