<?php

namespace App\Traits;

trait HasColors {

    private $colors_pattern = '/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/';

    public function setColorsSchemeAttribute($colors) {
        $colors_data = [];
        $default_colors = config('site.appearance.default_colors_scheme');
        foreach ($default_colors as $key => $field) {
            if (!isset($colors[$key]) || !(bool)preg_match($this->colors_pattern, $colors[$key])) {
                $colors_data[$key] = $default_colors[$key];
            } else {
                $colors_data[$key] = $colors[$key];
            }
        }
        $this->attributes['colors_scheme'] = json_encode($colors_data);
    }

    public function getColorsSchemeAttribute($colors) {
        $object_colors = json_decode($colors);
        if (!$object_colors) {
            return config('site.appearance.default_colors_scheme');
        }
        return $object_colors;
    }

}
