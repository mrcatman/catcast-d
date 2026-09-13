<?php
namespace App\Models\SettingsModels\Channel;


class RecordSettingsModel extends ChannelSettingsModel {

    protected $key = 'recording';

    protected $rights = ['live_broadcast'];

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
        'record_all' => 'boolean'
    ];
}
