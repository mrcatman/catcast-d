<?php

namespace App\Models\SettingsModels;

use App\Models\AdditionalSettings;
use Illuminate\Support\Arr;

class SettingsModel {

    protected $key;
    protected $fields;
    protected $permissions = [];
    protected $validation_rules = [];
    protected $entity_type;

    public function getKey() {
        return $this->key;
    }

    public function getFields() {
        return $this->fields;
    }

    protected function hasPermissions($entity) {
        return true;
    }

    public function setEntityType($entity_type) {
        $this->entity_type = $entity_type;
    }

    public function getValidationRules() {
        $rules = [];
        foreach ($this->validation_rules as $key => $value) {
            $new_key = 'additional_settings.'.$this->key.'.'.$key;
            $rules[$new_key] = $value;
        }
        return $rules;
    }

    protected function beforeSave($entity, $data) {
        return $data;
    }

    protected function afterSave($entity, $data) {

    }

    protected function canBeUsed($entity) {
        return true;
    }

    public function getValues($entity) {
        $values = [];
        $settings = AdditionalSettings::where(['entity_type' => $this->entity_type, 'entity_id' => $entity->id, 'key' => $this->getKey()])->first();
        $has_permissions = $this->hasPermissions($entity) && $this->canBeUsed($entity);
        if ($settings) {
            foreach ($settings->values as $key => $value) {
                $values[$key] = $value;
            }
        }
        $keys = [];
        foreach ($this->getFields() as $field) {
            if ($has_permissions || !isset($field['restricted']) || $field['restricted'] === false) {
                $keys[] = $field['field_id'];
            }
            if (!isset($values[$field['field_id']])) {
                if (isset($field['default_value']) && $field['default_value'] !== null) {
                    $values[$field['field_id']] = $field['default_value'];
                   } else {
                    $values[$field['field_id']] = "";
                }
            }
        }
        $values = Arr::only($values, $keys);
        return $values;
    }

    public function saveValues($entity, $data) {
        if ($this->hasPermissions($entity)) {
            $data = $this->beforeSave($entity, $data);
            $settings = AdditionalSettings::firstOrNew(['entity_type' => $this->entity_type, 'entity_id' => $entity->id, 'key' => $this->getKey()]);
            $default_settings = $this->getValues($entity);
            foreach ($default_settings as $key => $val) {
                if (!isset($data[$key])) {
                    $data[$key] = $settings->values[$key] ?? $default_settings[$key];
                }
            }
            $settings->key = $this->getKey();
            $settings->values = $data;
            $settings->save();
            $this->afterSave($entity, $data);
        }
    }

}
