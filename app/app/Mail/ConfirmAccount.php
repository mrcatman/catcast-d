<?php

namespace App\Mail;

use App\Helpers\LocalizationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmAccount extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $link;

    public function __construct($link)
    {
        $this->user = auth()->user();
        $this->link = $link;
    }

    public function build() {
        return $this->view('emails.confirm_account')->subject(LocalizationHelper::translate('connections.email.confirm_header'))->with([
            'username'=>$this->user->username,
            'link'=>$this->link
        ]);
    }
}
