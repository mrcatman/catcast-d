<?php
namespace App\Models\SettingsModels\Common;

use App\Models\SettingsModels\SettingsModel;

class PrivacySettingsModel extends SettingsModel {

    protected $key = 'privacy';

    protected $permissions = [];

    protected $fields = [
        [
            'field_id' => 'comments_enabled',
            'default_value' => true
        ],
        [
            'field_id' => 'comments_display',
            'default_value' => true
        ],
        [
            'field_id' => 'has_nsfw_content',
            'default_value' => false
        ],
        [
            'field_id' => 'rating_enabled',
            'default_value' => true
        ]
    ];

    protected $validation_rules = [
        'comments_enabled' => 'boolean',
        'comments_display' => 'boolean',
        'has_nsfw_content' => 'boolean'
    ];
}
