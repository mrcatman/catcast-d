<?php
namespace App\Models\SettingsModels\Channel;

use App\Models\Broadcast;

class DefaultBroadcastMetadataSettingsModel extends ChannelSettingsModel {

    protected $key = 'default_broadcast_metadata';

    protected $permissions = ['live_broadcast'];

    protected $fields = [
        [
            'field_id' => 'title',
            'default_value' => 'Live'
        ],
        [
            'field_id' => 'description',
            'default_value' => ''
        ],
        [
            'field_id' => 'category_id',
            'default_value' => null
        ],
        [
            'field_id' => 'tags',
            'default_value' => []
        ],
    ];

    protected $validation_rules = [
        'title' => 'sometimes|string|max:256',
        'description' => 'sometimes|string|max:1024',
        'category_id' => 'sometimes',
        'tags' => 'sometimes|array'
    ];

}
