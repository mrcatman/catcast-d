<?php
namespace App\Models\SettingsModels\User;

use App\Models\SettingsModels\SettingsModel;

class UserSettingsModel extends SettingsModel {

    protected $permissions = [];

    protected function hasPermissions($entity) {
        return auth()->user() && auth()->user()->id === $entity->id;
    }

}
