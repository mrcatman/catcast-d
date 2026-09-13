<?php

namespace App\Notifications\Types;


use App\Models\Channel;
use App\Models\User;
use App\Notifications\Channels\DatabaseExtendedChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\LocalizationHelper;
use NotificationChannels\Telegram\TelegramMessage;
use App\Notifications\Channels\VK\Message as VKMessage;

class NewPermissionRequest extends BaseNotificationType {

    protected $user;

    protected $default_channels = [DatabaseExtendedChannel::class];

    public function __construct($data = null) {
        $this->user = auth()->user();
        parent::__construct($data);
    }


    public static function getEventTypeId() {
        return 'permission_request';
    }

    public function getEntity() {
        return $this->user;
    }

    public function getTitle() {
        $text = LocalizationHelper::translate('notifications.types.new_permission_request.heading');
        $text.= $this->data->entity_name;
        return $text;
    }

    public function getRelativeUrl() {
        return '/user/permissions';
    }

    public function toMail() {
        return (new MailMessage)->subject($this->getTitle())->view('emails.new_permission_request', [
            'user' => auth()->user(),
            'permission' => $this->data,
            'url' => $this->getFullURL(),
        ]);
    }

    public function toTelegram($notifiable) {
        $url = $this->getFullURL();
        $content = PHP_EOL.PHP_EOL."*".($this->data->entity_name)."*";
        return TelegramMessage::create()
            ->to($notifiable->telegram_id)
            ->content($content)
            ->button(LocalizationHelper::translate("notifications.types.new_permission_request._button_text"), $url);
    }

    public function toVK() {
        $url = $this->getFullURL();
        $content = LocalizationHelper::translate('notifications.types.new_permission_request.heading').$this->data->entity_name.PHP_EOL.PHP_EOL.$this->data->title.PHP_EOL.$url;
        return (new VKMessage())->content($content);
    }

    public function toBroadcast($notifiable) {
        return new BroadcastMessage([
            'notification_type' => 'new_permission_request',
            'title' => 'notifications.types.new_permission_request.heading',
            'picture' => "",
            'text' => $this->data->entity_name,
            'translate' => ['title'],
            'notification_link' => $this->getRelativeURL(),
        ]);
    }

    public function toDatabase($notifiable) {
        return [
            'permission_id' => $this->data->id,
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
            ],
            'data' => $this->data,
        ];
    }


}
