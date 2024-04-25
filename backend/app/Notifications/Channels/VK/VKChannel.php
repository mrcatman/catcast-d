<?php

namespace App\Notifications\Channels;

use App\ExternalServiceConnections\VKExternalServiceConnection;
use Illuminate\Notifications\Notification;

class VKChannel
{
    protected $token;
    protected $text = "";
    public function __construct() {
        $this->token = config('services.vkontakte.token');
    }

    public function text($text) {
        $this->text = $text;
        return $this;
    }

    public function send($notifiable, Notification $notification)
    {
        $connection = (new VKExternalServiceConnection())->findByUserId($notifiable->id, true);
        if ($connection) {
            $message = $notification->toVK($notifiable, $connection);
            $request_params = [
                'message' => $message->getContent(),
                'user_id' => $connection->account_name,
                'access_token' => $this->token,
                'v' => '5.87'
            ];
            if ($attachment_id = $message->getAttachmentId()) {
                $request_params['attachment'] = $attachment_id;
            }
            $ch = curl_init('https://api.vk.com/method/messages.send');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        }
    }
}
