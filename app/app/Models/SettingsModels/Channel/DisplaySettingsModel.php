<?php
namespace App\Models\SettingsModels\Channel;

class DisplaySettingsModel extends ChannelSettingsModel {

    protected $key = 'display';

    protected $permissions = ['edit_info', 'live_broadcast'];

    protected $fields = [
        [
            'field_id' => 'show_in_autopilot_mode',
            'default_value' => true
        ],
        [
            'field_id' => 'hide_autopilot_timetable',
            'default_value' => false
        ],
        [
            'field_id' => 'protect_with_password',
            'default_value' => false
        ],
        [
            'field_id' => 'watch_password',
            'default_value' => ''
        ],
    ];

    protected $validation_rules = [
        'show_in_autopilot_mode' => 'boolean',
        'hide_autopilot_timetable' => 'boolean',
        'protect_with_password' => 'boolean',
        'watch_password' => 'sometimes|max:50'
    ];
}
