<?php

namespace App\Notifications\Types;

use App\Models\Announce;

use App\Models\Broadcast;
use App\Models\Channel;
use App\Models\User;
use App\Notifications\Channels\DatabaseExtendedChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\LocalizationHelper;
use NotificationChannels\Telegram\TelegramMessage;
use NotificationChannels\Telegram\TelegramFile;
use App\Notifications\Channels\VK\Message as VKMessage;

class NewBroadcast extends BaseNotificationType {

    protected $default_channels = [DatabaseExtendedChannel::class];

    protected $broadcast = null;

    protected $is_radio = false;

    public function __construct(Broadcast $broadcast) {
        $this->broadcast = $broadcast;
    }

    public static function getEventTypeId() {
        return 'new_broadcast';
    }

    public function getEntity() {
        return $this->broadcast;
    }

    public static function getEntityClass() {
        return Broadcast::class;
    }

    public function getTitle() {
        return LocalizationHelper::translate('notifications.types.new_broadcast.heading_full', [
            'channel_name' => $this->broadcast->channel->name,
            'broadcast_title' => $this->broadcast->title,
        ]);
    }

    public function getRelativeUrl() {
        return "/".$this->broadcast->channel->shortname;
    }

    public function toMail() {
        return (new MailMessage)->subject($this->getTitle())->view('emails.new_broadcast', [
            'is_radio' => $this->is_radio,
            'broadcast' => $this->broadcast,
            'url' => $this->getFullURL(),
        ]);
    }

    public function toTelegram($notifiable) {
        // todo
        $url = $this->getFullURL();
        $text = "";
        if ($this->is_radio) {
            $text = LocalizationHelper::translate('notifications.types.new_broadcast.heading_1_radio');
            $text .= '*"' . $this->data->name . '*"';
            $text .= LocalizationHelper::translate('notifications.types.new_broadcast.heading_2_radio');
        } else {
            $text = LocalizationHelper::translate('notifications.types.new_broadcast.heading_1_tv');
            $text .= '*"' . $this->data->name . '*"';
            $text .= LocalizationHelper::translate('notifications.types.new_broadcast.heading_2_tv');
        }
        if ($this->program['title'] != '') {
            $text.= PHP_EOL.PHP_EOL;
            $text.= LocalizationHelper::translate('notifications.types.new_broadcast.now_broadcasting');
            if (isset($this->program['user'])) {
                $text.= "*".$this->program['user']->username."* ".LocalizationHelper::translate('notifications.types.new_broadcast.user_broadcasts');
            }
            $text.= "*".$this->program['title']."*";
        }
        if (isset($this->program['picture'])) {
            $message = TelegramFile::create();
            $message->file($this->program['picture'], 'photo');
        } else {
            $message = TelegramMessage::create();
        }
        $button_text = $this->is_radio ? LocalizationHelper::translate("notifications.types.new_broadcast._button_text_radio") : LocalizationHelper::translate("notifications.types.new_broadcast._button_text_tv");
        return $message
            ->to($notifiable->telegram_id)
            ->content($text)
            ->button($button_text, $url);
    }

    public function toVK() {
        $url = $this->getFullURL();
        $text = "";
        if ($this->is_radio) {
            $text = LocalizationHelper::translate('notifications.types.new_broadcast.heading_1_radio');
            $text .= '"' . $this->data->name . '"';
            $text .= LocalizationHelper::translate('notifications.types.new_broadcast.heading_2_radio');
        } else {
            $text = LocalizationHelper::translate('notifications.types.new_broadcast.heading_1_tv');
            $text .= '"' . $this->data->name . '"';
            $text .= LocalizationHelper::translate('notifications.types.new_broadcast.heading_2_tv');
        }
        if ($this->program['title'] != '') {
            $text.= PHP_EOL.PHP_EOL;
            $text.= LocalizationHelper::translate('notifications.types.new_broadcast.now_broadcasting');
            if (isset($this->program['user'])) {
                $text.= $this->program['user']->username." ".LocalizationHelper::translate('notifications.types.new_broadcast.user_broadcasts');
            }
            $text.= $this->program['title'];
        }
        $text.= PHP_EOL.$url;
        $message = (new VKMessage());
        if (isset($this->program['picture'])) {
            $message->picture($this->program['picture']);
        }
        return $message->content($text);
    }

    public function toBroadcast($notifiable) {
        return new BroadcastMessage([
            'title' => $this->getTitle(),
            'picture' => $this->broadcast->channel->logo,
            'text' => $this->broadcast->description,
            'url' => $this->getRelativeURL(),
        ]);
    }


}
