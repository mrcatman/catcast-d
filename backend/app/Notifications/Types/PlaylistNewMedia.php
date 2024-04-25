<?php

namespace App\Notifications\Types;

use App\Models\Media;
use App\Notifications\Channels\DatabaseExtendedChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\LocalizationHelper;

use NotificationChannels\Telegram\TelegramFile;
use App\Notifications\Channels\VK\Message as VKMessage;

class PlaylistNewMedia extends BaseNotificationType {

    protected $needs_entity_id = true;
    protected $default_channels = [DatabaseExtendedChannel::class];

    protected $is_radio = false;
    protected $playlist = null;

    public function __construct($data = null, $is_radio = false) {
        $this->is_radio = $is_radio;
        if ($data) {
            $this->playlist = $is_radio ? $data->site_folder->playlist : $data->playlist;
        }
        parent::__construct($data);
    }

    public static function getEventTypeId() {
        return 'playlist_new_media';
    }


    public function getEntity() {
        return $this->media;
    }


    public function getTitle() {
        $text = LocalizationHelper::translate('notifications.texts.new_media.title');
        $text.= $this->playlist->name;
        return $text;
    }

    public function getLink() {
        return $this->is_radio ? "records/".$this->data->id : "videos/".$this->data->id;
    }

    public function toMail() {
        return (new MailMessage)->subject($this->getTitle())->view('emails.playlists_new_recording', [
            'domain' =>  config('app.client_host'),
            'playlist' => $this->playlist,
            'channel' => $this->data->channel,
            'is_radio' => $this->is_radio,
            'data' => $this->data,
            'lang' => $this->lang,
            'url' => $this->getFullURL(),
        ]);
    }

    public function toTelegram($notifiable) {
        $url = $this->getFullURL();
        $content = LocalizationHelper::translate('notifications.texts.playlists_new_recording.text_1').' *'.$this->data->channel->name.'* '.LocalizationHelper::translate('notifications.texts.playlists_new_recording.text_2').PHP_EOL.PHP_EOL."*".($this->data->title)."*";
        $message = TelegramFile::create()
            ->to($notifiable->telegram_id)
            ->content($content)
            ->button($this->is_radio ? LocalizationHelper::translate("notifications.texts.playlists_new_recording._button_text_radio") : LocalizationHelper::translate("notifications.texts.playlists_new_recording._button_text_tv"), $url);
        if ($this->data->thumbnail) {
            $message->file($this->data->thumbnail, 'photo');
        }
        return $message;
    }

    public function toVK() {
        $url = $this->getFullURL();
        $content = LocalizationHelper::translate('notifications.texts.playlists_new_recording.text_1').' '.$this->data->channel->name.' '.LocalizationHelper::translate('notifications.texts.playlists_new_recording.text_2').PHP_EOL.PHP_EOL.($this->data->title).PHP_EOL.PHP_EOL.$url;
        $message = (new VKMessage())->content($content);
        if ($this->data->thumbnail) {
            $message->picture($this->data->thumbnail);
        }
        return $message;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notification_type' => 'playlistNewRecording',
            'title' => LocalizationHelper::translate('notifications.texts.playlists_new_recording.title').$this->data->channel->name,
            'picture' => $this->data->channel->logo,
            'text' => $this->data->title,
            'translate' => [],
            'notification_link' => $this->getRelativeURL(),
            'notification_data' => $this->data,
        ]);
    }


    public function toDatabase($notifiable) {
        return [
            'is_radio' => $this->is_radio,
            'channel' => [
                'id' => $this->data->channel->id,
                'shortname' => $this->data->channel->shortname,
                'name' => $this->data->channel->name,
                'logo' => $this->data->channel->logo,
            ],
            'playlist' => [
                'id' => $this->playlist->id,
                'name' => $this->playlist->name,
                'logo' => $this->playlist->logo,
            ],
            'data' => [
                'id' => $this->data->id,
                'title' => $this->data->title,
                'time' => $this->is_radio ? $this->data->created_at->timestamp : $this->data->add_time,
            ]
        ];
    }
}
