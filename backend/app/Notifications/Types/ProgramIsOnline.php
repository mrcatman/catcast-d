<?php

namespace App\Notifications\Types;

use App\Models\Announce;
use App\Autopilot\AutopilotFolder;
use App\Autopilot\AutopilotItem;
use App\Autopilot\AutopilotPlaylist;
use App\Models\Channel;

use App\Notifications\Channels\DatabaseExtendedChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\LocalizationHelper;
use NotificationChannels\Telegram\TelegramFile;
use App\Notifications\Channels\VK\Message as VKMessage;

class ProgramIsOnline extends BaseNotificationType {

    protected $item = null;
    protected $channel = null;
    protected $default_channels = [DatabaseExtendedChannel::class];

    public function __construct($data = null) {
        if ($data) {
            switch ($data->program_type) {
                case 'autopilot_item':
                    $item = AutopilotItem::find($data->program_id);
                    break;
                case 'autopilot_playlist':
                    $item = AutopilotPlaylist::find($data->program_id);
                    break;
                case 'autopilot_folder':
                    $item = AutopilotFolder::find($data->program_id);
                    break;
                case 'announce':
                    $item = Announce::find($data->program_id);
                    break;
                default:
                    break;
            }
            $this->channel = Channel::find($data->channel_id);
            $this->item = $item;
        }
        parent::__construct($data);
    }

    public static function getEventTypeId() {
        return 'program_is_online';
    }


    public function getEntity() {
        return Announce::class;
    }

    public function getEntityId() {
        return $this->data->channel_id;
    }

    public function getEntityName() {
        if (!$this->item) {
            return null;
        }
        $title = null;
        switch ($this->data->program_type) {
            case 'autopilot_item':
            case 'autopilot_folder':
            case 'announce':
                $title = $this->item->title;
                break;
            case 'autopilot_playlist':
                $title = $this->item->data->title;
                break;
            default:
                break;
        }
        return $title;
    }

    public function getPicture() {
        if (!$this->item) {
            return null;
        }
        $picture = null;
        switch ($this->data->program_type) {
            case 'autopilot_item':
                $picture = $this->item->picture;
                break;
            case 'autopilot_folder':
                break;
            case 'announce':
                $picture = $this->item->cover;
                break;
            case 'autopilot_playlist':
                break;
            default:
                break;
        }
        return $picture;
    }

    public function getTitle() {
        $entity_name = $this->getEntityName();
        if ($entity_name) {
            $text = '"' . $entity_name . '" ';
            $text .= LocalizationHelper::translate('notifications.texts.program_is_online.heading');
            return $text;
        } else {
            return null;
        }
    }

    public function getLink() {
        return "/".$this->channel->shortname;
    }

    public function toMail() {
        return (new MailMessage)->subject($this->getTitle())->view('emails.program_is_online', [
            'title' => $this->getEntityName(),
            'channel' => $this->channel,
            'data' => $this->item,
            'picture' => $this->getPicture(),
            'url' => $this->getFullURL(),
        ]);
    }

    public function toTelegram($notifiable) {
        $url = $this->getFullURL();
        $content = LocalizationHelper::translate("notifications.texts.program_is_online.text_1")." *".$this->channel->name." *".LocalizationHelper::translate("notifications.texts.program_is_online.text_2");
        $content.=PHP_EOL.PHP_EOL;
        $content.="*".$this->getEntityName()."*";
        $message =  TelegramFile::create()
            ->to($notifiable->telegram_id)
            ->content($content)
            ->button(LocalizationHelper::translate("notifications.texts.program_is_online._button_text"), $url);
        if ($picture = $this->getPicture()) {
            $message->file($picture, 'photo');
        }
        return $message;
    }

    public function toVK($notifiable, $connection) {
        $url = $this->getFullURL();
        $content = LocalizationHelper::translate("notifications.texts.program_is_online.text_1")." ".$this->channel->name." ".LocalizationHelper::translate("notifications.texts.program_is_online.text_2");
        $content.=PHP_EOL.PHP_EOL;
        $content.=$this->getEntityName();
        $content.=PHP_EOL.$url;
        $message = (new VKMessage())->content($content)->userId($connection->account_name);
        if ($picture = $this->getPicture()) {
            $message->picture($picture);
        }
        return $message;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notification_type' => 'ProgramIsOnline',
            'title' => $this->getEntityName(),
            'picture' => $this->channel->logo,
            'text' => LocalizationHelper::translate('notifications.texts.program_is_online.heading'),
            'translate' => [],
            'notification_link' => $this->getRelativeURL()
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->getEntityName(),
            'picture' => $this->getPicture(),
            'channel' => [
                'id' => $this->channel->id,
                'shortname' => $this->channel->shortname,
                'name' => $this->channel->name,
                'logo' => $this->channel->logo,
            ],
            'data' => $this->data,
        ];
    }
}

