<?php
namespace App\Models\SettingsModels\Audio;

use App\Models\Channel;
use App\Models\SettingsModels\SettingsModel;

class AudioMetadataSettingsModel extends SettingsModel {

    protected $key = 'metadata';

    protected $permissions = ['media'];

    protected $fields = [
        [
            'field_id' => 'title',
            'default_value' => ''
        ],
        [
            'field_id' => 'artist',
            'default_value' => ''
        ],
        [
            'field_id' => 'album',
            'default_value' => ''
        ],
    ];

    protected $validation_rules = [
        'title' => 'sometimes|max:256',
        'artist' => 'sometimes|max:256',
        'album' => 'sometimes|max:256',
    ];

    protected function canBeUsed($entity) {
        return $entity->is_radio;
    }
}
