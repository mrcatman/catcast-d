<?php
namespace App\Models\SettingsModels\User;

class PrivacySettingsModel extends UserSettingsModel {

    protected $key = 'privacy';

    protected $permissions = [];

    protected $fields = [
        [
            'field_id' => 'can_view_profile',
            'default_value' => 'all'
        ],
        [
            'field_id' => 'can_write_on_wall',
            'default_value' => 'all'
        ],
    ];

    protected $validation_rules = [
        'can_view_profile' => 'in:all,friends,none',
        'can_write_on_wall' => 'in:all,friends,none',
    ];
}
