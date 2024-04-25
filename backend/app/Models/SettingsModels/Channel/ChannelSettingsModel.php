<?php
namespace App\Models\SettingsModels\Channel;

use App\Helpers\PermissionsHelper;
use App\Models\SettingsModels\SettingsModel;

class ChannelSettingsModel extends SettingsModel {

    protected $permissions = [];

    protected function hasPermissions($entity) {
        return PermissionsHelper::getStatus($this->permissions, $entity);
    }

}
