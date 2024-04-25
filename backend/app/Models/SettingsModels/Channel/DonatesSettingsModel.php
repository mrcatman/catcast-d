<?php
namespace App\Models\SettingsModels\Channel;

class DonatesSettingsModel extends ChannelSettingsModel {

    protected $key = "donates";

    protected $permissions = ['donates'];

    protected $fields = [
            //[
            //    'field_id' => 'wallet_type',
           //     'is_restricted' => true,
           // ],
          //  [
          //      'field_id' => 'wallet_id',
           //     'is_restricted' => true,
          //  ],
            [
                'field_id' => 'donate_comment',
            ],
            [
                'field_id' => 'minimal_sum',
                'default_value' => 10,
            ],
            [
                'field_id' => 'minimal_visible_sum',
                'default_value' => 50,
            ],
            [
                'field_id' => 'show_in_player',
                'default_value' => true,
            ],
            [
                'field_id' => 'show_last_donates',
                'default_value' => true,
            ],
            [
                'field_id' => 'button_text',
            ],
            [
                'field_id' => 'accept_from_guests',
                'default_value' => true,
            ],
    ];

    protected $validation_rules = [
        'minimal_sum' => 'numeric|min:0',
        'minimal_visible_sum' => 'numeric|min:0',
        'show_in_player' => 'boolean',
        'show_last_donates' => 'boolean',
    ];
}
