<?php
namespace App\Traits;

use App\Helpers\ConfigHelper;
use App\Models\Picture;
use App\Models\PictureConnection;

trait HasPictures {

    public function getPicturesDataAttribute() {
        $data = [];
        foreach ($this->pictures_fields as $type) {
            $data[$type] = $this->pictures->filter(function($item) use ($type) {
                return $type == $item->pivot->entity_picture_type;
            })->first();
            if (!$data[$type]) {
                $data[$type] = (object)[
                    'id' => -1,
                    'full_url' => null
                ];
            }
        }
        return $data;
    }

    protected function getPicture($type, $fallback = "/assets/pictures/no-logo.svg") {
        $pictures = $this->pictures->filter(function($item) use ($type) {
            return $type == $item->pivot->entity_picture_type;
        });
        if ($pictures->count() === 0) {
            if ($fallback) {
                return ConfigHelper::siteURL() . $fallback;
            }
            return null;
        }
        return $pictures->last()->full_url;
    }

    public function setPicturesDataAttribute($data) {
        if (is_array($data)) {
            foreach ($data as $field => $val) {
                if (in_array($field, $this->pictures_fields)) {
                    $connection = PictureConnection::firstOrNew([
                        'entity_id' => $this->id,
                        'entity_type' => self::getEntityType(),
                        'entity_picture_type' => $field
                    ]);
                    if ($val['id'] > 0) {
                        $picture = Picture::find($val['id']);
                        if (!$picture) {
                            $messages = [
                                $field => ['errors.item_not_found']
                            ];
                            throw \Illuminate\Validation\ValidationException::withMessages($messages);
                         } else {
                            $connection->picture_id = $picture->id;
                            $connection->entity_id = $this->id;
                            $connection->entity_type = self::getEntityType();
                            $connection->entity_picture_type = $field;
                            $connection->save();
                        }
                    } else {
                        if ($connection->exists) {
                            $connection->delete();
                        }
                    }
                }
            }
        }
    }

    public function pictures() {
        return $this->belongsToMany(
            Picture::class,
            'pictures_connections',
            'entity_id',
            'picture_id'
        )->withPivot(['entity_picture_type'])->wherePivot('entity_type', self::getEntityType());
    }

}
