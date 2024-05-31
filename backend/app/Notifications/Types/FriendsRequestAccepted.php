<?php

namespace App\Notifications\Types;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\LocalizationHelper;

use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\Telegram\TelegramMessage;
use App\Notifications\Channels\VK\Message as VKMessage;

class FriendsRequestAccepted extends BaseNotificationType {

    protected $default_channels = [];

    protected $data;

    protected $me;

    public function __construct($user = null, $me = null) {
        $this->data = $user;
        $this->me = $me;
        parent::__construct($user);
    }

    public static function getEventTypeId() {
        return 'friends_request_accepted';
    }

    public static function getDisplayName() {
        return 'notifications.types.users.subtypes.friends_request_accepted.heading';
    }

    public static function getEntity() {
        return User::class;
    }

    public function getEntityId() {
        return $this->data->id;
    }

    public function getRelativeUrl() {
        return "/users/".$this->me->id."?t=friends";
    }


    public function toTelegram($notifiable) {
        $url = $this->getFullURL();
        $content = "*".($this->data->username)."*".LocalizationHelper::translate('notifications.types.friends_request_accepted.full_title');
        return TelegramMessage::create()
            ->to($notifiable->telegram_id)
            ->content($content)
            ->button(LocalizationHelper::translate("notifications.types.friends_request_accepted._button_text"), $url);
    }

    public function toVK() {
        $url = $this->getFullURL();
        $content = $this->data->username.LocalizationHelper::translate('notifications.types.friends_request_accepted.full_title').PHP_EOL.PHP_EOL.$url;
        return (new VKMessage())->content($content);
    }


    public function toMail($notifiable) {
        return (new MailMessage)->subject($this->data->username.LocalizationHelper::translate('notifications.types.friends_request_accepted.full_title'))->view('emails.friends_request_accepted', [
            'data' => $this->data,
            'url' => $this->getFullURL(),
            'lang' => $this->lang,
        ]);
    }

    public function toBroadcast($notifiable) {
      return new BroadcastMessage([
          'notification_type' => 'FriendsRequestAccepted',
          'title' => 'notifications.types.friends_request_accepted.heading',
          'picture' => $this->data->avatar,
          'text' => $this->data->username,
          'notification_link' => $this->getRelativeURL()
          'translate' => ['title']
      ]);
    }
}
