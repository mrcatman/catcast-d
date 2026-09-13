<?php
namespace App\ExternalServiceConnections;

use App\ExternalServices\ExternalService;
use App\Helpers\ConfigHelper;
use App\Models\User;
use App\Models\AccountConnection;
use App\Models\AccountRestore;
use Illuminate\Support\Str;

class ExternalServiceConnection {
    protected $name;
    protected $connection_instance;
    protected $is_first_time = false;
    protected $should_send_confirmation = false;

    public function getName() {
        return $this->name;
    }

    public function shouldSendConfirmation() {
        return $this->should_send_confirmation;
    }
    public function sendConfirmation() {

    }

    public function connection($user = null) {
        $user = $user ? $user : auth()->user();
        if (!$user) {
            return null;
        }
        return AccountConnection::where(['user_id'=>$user->id])->where(['account_type'=>$this->name])->first();
    }

    public function findByExternalHandle($chat_id,$confirmed = false) {
        $connection = AccountConnection::where(['account_name'=>$chat_id])->where(['account_type'=>$this->name]);
        if ($confirmed) {
            $connection = $connection->where(['confirmed' => true]);
        }
        $connection = $connection->first();
        return $connection;
    }

    public function findByUserId($user_id,$confirmed = false) {
        $connection = AccountConnection::where(['user_id'=>$user_id])->where(['account_type'=>$this->name]);
        if ($confirmed) {
            $connection = $connection->where(['confirmed' => true]);
        }
        $connection = $connection->first();
        return $connection;
    }

    public function findByCode($code,$confirmed = false) {
        $connection = AccountConnection::where(['confirm_code'=>$code]);
        if ($confirmed) {
            $connection = $connection->where(['confirmed' => true]);
        }
        $connection = $connection->first();
        return $connection;
    }

    public function setFromCode($code) {
        $connection = $this->findByCode($code);
        if ($connection) {
            $this->connection_instance = $connection;
        }
        return $this;
    }

    public function createByExternalHandle($external_handle) {
        if ($connection = $this->findByExternalHandle($external_handle)) {
            $this->connection_instance = $connection;
            return $this;
        }
        $this->is_first_time = true;

        $this->connection_instance = new AccountConnection([
            'account_type'=>$this->name,
            'account_name'=>$external_handle,
            'confirm_status'=>-1
        ]);
        if ($user = auth()->user()) {
            $this->connection_instance->user_id = $user->id;
        }
        $this->connection_instance->save();
        return $this;
    }

    public function hasInstance() {
        return (bool)$this->connection_instance;
    }


    public function getLanguage() {
        if ($this->connection_instance && $this->connection_instance->language_code) {
            return $this->connection_instance->language_code;
        }
        return "ru";
    }

    public function setLanguage($language_code) {
        if (!$this->connection_instance) {
            return;
        }
        $this->connection_instance->language_code = $language_code;
        $this->connection_instance->save();
    }

    public function isNotConfirmed() {
        if (!$this->connection_instance) {
            return;
        }
        return $this->connection_instance->confirm_status == -1;
    }

    public function isWaitingForConfirmation() {
        if (!$this->connection_instance) {
            return;
        }
        return $this->connection_instance->confirm_status == 0;
    }


    public function isConfirmed() {
        if (!$this->connection_instance) {
            return;
        }
        return $this->connection_instance->confirm_status == 1;
    }

    public function canResendConfirmation() {
        if (!$this->connection_instance) {
            return;
        }
        return (time() - $this->connection_instance->updated_at->timestamp > 60);
    }

    public function startConfirmation() {
        if (!$this->connection_instance) {
            return;
        }
        $code = Str::random(20);
        $link = ConfigHelper::siteURL()."/auth/confirm?code=$code";
        $this->connection_instance->confirm_code = $code;
        $this->connection_instance->confirm_status = 0;
        $this->connection_instance->save();
        return $link;
    }

    public function startUserRestore() {
        $restore = new AccountRestore();
        $code = Str::random(20);
        $link = ConfigHelper::siteURL()."/auth/restore?code=$code";
        $restore->user_id = $this->connection_instance->user_id;
        $restore->connection_id = $this->connection_instance->id;
        $restore->confirm_code = $code;
        $restore->confirm_status = 0;
        $restore->save();
        return $link;
    }



    public function isFirstTime() {
        return $this->is_first_time;
    }

    public function confirm($user) {
        if (!$user) {
            $user = auth()->user();
        }
        if (!$user) {
            return;
        }
        if (!$this->connection_instance) {
            return;
        }
        $this->connection_instance->user_id = $user->id;
        $this->connection_instance->confirm_status = 1;
        $this->connection_instance->save();
    }

    public function deleteUnconfirmed() {
        AccountConnection::where(['account_type'=>$this->getName()])->where(['user_id'=>$this->connection_instance->user_id])->where('id','!=',$this->connection_instance->id)->delete();
        return $this;
    }
}
