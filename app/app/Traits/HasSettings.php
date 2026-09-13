<?php

namespace App\Traits;


trait HasSettings {

    public function getAdditionalSettingsAttribute() {
        $settings = [];
        foreach ($this->additional_settings_models as $additional_settings_model) {
            $instance = new $additional_settings_model;
            $instance->setEntityType(self::getEntityType());
            $settings[$instance->getKey()] = $instance->getValues($this);
        }
        return $settings;
    }

    public function setAdditionalSettingsAttribute($settings) {
        foreach ($this->additional_settings_models as $additional_settings_model) {
            $instance = new $additional_settings_model;
            $instance->setEntityType(self::getEntityType());
            if (isset($settings[$instance->getKey()])) {
                $instance->saveValues($this, $settings[$instance->getKey()]);
            }
        }
    }


}
