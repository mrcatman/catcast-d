<?php
namespace App\Models\SettingsModels\Channel;

use App\Models\SettingsModels\Common\PrivacySettingsModel as BasePrivacySettingsModel;

class PrivacySettingsModel extends ChannelSettingsModel {

    protected $permissions = [];

    protected $key = '';
    protected $fields = [];
    protected $validation_rules = [];

    public function __construct() {
        $privacy_settings_model = new BasePrivacySettingsModel();
        $this->key = $privacy_settings_model->getKey();
        $this->fields = $privacy_settings_model->getFields();
        $this->validation_rules = $privacy_settings_model->getValidationRules();
    }

}
