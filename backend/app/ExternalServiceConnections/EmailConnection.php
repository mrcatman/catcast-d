<?php

namespace App\ExternalServiceConnections;
use App\Mail\ConfirmAccount;
use App\Mail\RestoreUser;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\AccountConnection;

class EmailConnection extends ExternalServiceConnection {
    protected $name = "email";
    public $should_send_confirmation = true;

    public function createByExternalHandle($external_handle) {
        $user = User::where(['email'=>$external_handle])->first();
        if ($user ) {
            $this->connection_instance = new AccountConnection([
                'account_type'=>$this->name,
                'account_name'=>$external_handle,
                'confirmed' => true,
                'user_id'=>$user->id
            ]);
        }
        return parent::createByExternalHandle($external_handle);
    }

    public function findByUserId($user_id, $confirmed = false) {
        $user = User::find($user_id);
        if ($user->email && $user->email !== '') {
            return new AccountConnection([
                'account_type'=>$this->name,
                'account_name'=>$user->email,
                'confirmed' => true,
                'user_id'=>$user->id
            ]);
        }
        return parent::findByUserId($user_id, $confirmed);
    }

    public function findByExternalHandle($chat_id, $confirmed = false) {
       $user = User::where(['email' => $chat_id])->first();
       if ($user) {
            return new AccountConnection([
                'account_type'=>$this->name,
                'account_name'=>$user->email,
                'confirmed' => true,
                'user_id'=>$user->id
            ]);
        }
        return parent::findByExternalHandle($chat_id, $confirmed);
    }

    public function sendConfirmation()
    {
        $email = $this->connection_instance->account_name;
        $link = $this->startConfirmation();
        Mail::to($email)->send(new ConfirmAccount($link));
    }

    public function sendRestoreUserLink()
    {
        $email = $this->connection_instance->account_name;
        $user = $this->connection_instance->user;
        $link = $this->startUserRestore();
        Mail::to($email)->send(new RestoreUser($user, $link));
    }
}
