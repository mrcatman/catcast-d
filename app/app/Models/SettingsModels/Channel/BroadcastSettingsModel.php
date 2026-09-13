<?php
namespace App\Models\SettingsModels\Channel;

use App\Models\Broadcast;

class BroadcastSettingsModel extends ChannelSettingsModel {

    protected $key = "broadcast";

    protected $permissions = ['live_broadcast'];

    protected $fields = [
        [
            'field_id' => 'records_public',
            'default_value' => true
        ],
        [
            'field_id' => 'record_all',
            'default_value' => false
        ],
    ];

    protected $validation_rules = [
        'records_public' => 'boolean',
        'record_all' => 'boolean',
    ];

}
