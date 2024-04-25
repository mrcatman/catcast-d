<?php

namespace App\Mail;

use App\Helpers\LocalizationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RestoreUser extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $link;

    public function __construct($user, $link)
    {
        $this->user = $user;
        $this->link = $link;
    }

    public function build() {
        return $this->view('emails.restore_account')->subject(LocalizationHelper::translate('connections.email.restore_header'))->with([
            'username'=>$this->user->username,
            'link'=>$this->link
        ]);
    }
}
