<?php
namespace App\Models\SettingsModels\Channel;

use App\Events\Chat\ChatChangeStateEvent;
use App\Models\Picture;
use App\Rules\PictureId;

class ChatSettingsModel extends ChannelSettingsModel {

    protected $key = 'chat';

    protected $permissions = ['edit_info', 'moderation'];

    protected $fields = [
        [
            'field_id' => 'allow_guests',
            'default_value' => true
        ],
        [
            'field_id' => 'default_guest_username',
            'default_value' => 'Guest'
        ],
        [
            'field_id' => 'forbidden_words',
            'default_value' => []
        ],
        [
            'field_id' => 'disabled',
            'default_value' => false
        ],
        [
            'field_id' => 'motd',
            'default_value' => ''
        ],
        [
            'field_id' => 'smileys',
            'default_value' => []
        ],
    ];

    protected $validation_rules = [

    ];

    public function __construct() {
        $max_smileys_count = config('site.max_custom_smileys_count', 10);
        $this->validation_rules = [
            'allow_guests' => 'boolean',
            'default_guest_username' => 'string|max:50',
            'forbidden_words' => 'array',
            'forbidden_words.*.word' => 'min:1',
            'disabled' => 'boolean',
            'motd' => 'string|max:512',
            'smileys' => 'array|max:'.$max_smileys_count,
            'smileys.*.code' => 'sometimes',
            'smileys.*.id' => ['required', new PictureId],
        ];
    }

    protected function beforeSave($channel, $data) {
       if (isset($data['smileys'])) {
           foreach ($data['smileys'] as &$smiley) {
               $picture = Picture::find($smiley['id']);
               $smiley['full_url'] = $picture->full_url;
           }
       }
       return $data;
    }

    protected function afterSave($channel, $data) {
        broadcast(new ChatChangeStateEvent($channel, auth()->user(), $data));
    }
}
