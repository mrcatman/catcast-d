<?php

namespace App\ExternalServiceConnections;

use App\Models\SocialConnection;
use App\Models\AccountConnection;
use App\Models\User;

class VKExternalServiceConnection extends ExternalServiceConnection {
    protected $name = "vk";
    public function createByExternalHandle($external_handle) {
        $social_connection_instance = SocialConnection::where(['provider_name'=>'vkontakte'])->where(['provider_user_id'=>$external_handle])->first();
        if ($social_connection_instance) {
            $this->connection_instance = new AccountConnection([
                'account_type'=>$this->name,
                'account_name'=>$external_handle,
                'confirm_status'=>1,
                'user_id'=>$social_connection_instance->user_id
            ]);
        } else {
            $user = User::where(['vkid'=>$external_handle])->first();
            if ($user ) {
                $this->connection_instance = new AccountConnection([
                    'account_type'=>$this->name,
                    'account_name'=>$external_handle,
                    'confirm_status'=>1,
                    'user_id'=>$user->id
                ]);
            }
        }
        return parent::createByExternalHandle($external_handle);
    }

    public function findByUserId($user_id, $confirmed = false) {
        $social_connection_instance = SocialConnection::where(['provider_name'=>'vkontakte'])->where(['user_id'=>$user_id])->first();
        if ($social_connection_instance) {
            return new AccountConnection([
                'account_type'=>$this->name,
                'account_name'=>$social_connection_instance->provider_user_id,
                'confirm_status'=>1,
                'user_id'=>$social_connection_instance->user_id
            ]);
        } else {
            $user = User::find($user_id);
            if ($user->vkid && $user->vkid > 0) {
                return new AccountConnection([
                    'account_type'=>$this->name,
                    'account_name'=>$user->vkid,
                    'confirm_status'=>1,
                    'user_id'=>$user->id
                ]);
            }
        }
        return parent::findByUserId($user_id, $confirmed);
    }
}
