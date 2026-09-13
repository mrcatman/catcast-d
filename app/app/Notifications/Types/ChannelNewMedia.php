<?php

namespace App\Notifications\Types;

use App\Models\Media;
use App\Models\NotificationSubscription;
use App\Notifications\Channels\DatabaseExtendedChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\LocalizationHelper;
use Illuminate\Support\Collection;
use NotificationChannels\Telegram\TelegramFile;
use App\Notifications\Channels\VK\Message as VKMessage;

class ChannelNewMedia extends BaseNotificationType {

    protected $default_channels = [DatabaseExtendedChannel::class];
    protected $is_radio = false;
    protected $playlist_id = null;

    public function __construct($data = null) {
        if ($data) {
            $this->is_radio = $data->channel->is_radio;
            $this->playlist_id = $data->channel->is_radio ? ($data->site_folder ? $data->site_folder->connected_playlist_id : null) : $data->playlist_id;
        }
        parent::__construct($data);
    }

    public static function getEventTypeId() {
        return 'channel_new_media';
    }


    public function getEntity() {
        return $this->data;
    }

    public function getTitle() {
        $text = $this->is_radio ? LocalizationHelper::translate('notifications.types.channels_new_recording.heading_radio') : LocalizationHelper::translate('notifications.types.channels_new_recording.heading_tv');
        $text.= $this->data->channel->name;
        return $text;
    }

    public function filterUsers(Collection $users) {
        if ($this->playlist_id) {
            $playlist_notifications_user_ids = NotificationSubscription::where(['type'=> PlaylistNewMedia::getEventTypeId(), 'entity_id' => $this->playlist_id])->pluck('user_id');
            $users = array_filter($users, function ($user) use ($playlist_notifications_user_ids) {
                return !$playlist_notifications_user_ids->contains($user->id);
            });
        }
        return $users;
    }

    public function getRelativeUrl() {
        return 'media/'.$this->data->id;
    }

    public function toMail() {
        return (new MailMessage)->subject($this->getTitle())->view('emails.channels_new_recording', [
            'is_radio' => $this->is_radio,
            'domain' =>  config('app.client_host'),
            'channel' => $this->data->channel,
            'data' => $this->data,
            'lang' => $this->lang,
            'url' => $this->getFullURL(),
        ]);
    }

    public function toTelegram($notifiable) {
        $url = $this->getFullURL();
        if ($this->is_radio) {
            $content = LocalizationHelper::translate('notifications.types.channels_new_recording.text_1_radio').' *'.$this->data->channel->name.'* '.LocalizationHelper::translate('notifications.types.channels_new_recording.text_2_radio').PHP_EOL.PHP_EOL."*".($this->data->title)."*";
        } else {
            $content = LocalizationHelper::translate('notifications.types.channels_new_recording.text_1_tv').' *'.$this->data->channel->name.'* '.LocalizationHelper::translate('notifications.types.channels_new_recording.text_2_tv').PHP_EOL.PHP_EOL."*".($this->data->title)."*";
        }
         $message = TelegramFile::create()
            ->to($notifiable->telegram_id)
            ->content($content)
            ->button($this->is_radio ?  LocalizationHelper::translate("notifications.types.channels_new_recording._button_text_radio") : LocalizationHelper::translate("notifications.types.channels_new_recording._button_text_tv"), $url);
        if ($this->data->thumbnail) {
            $message->file($this->data->thumbnail, 'photo');
        }
        return $message;
    }

    public function toVK() {
        $url = $this->getFullURL();
        $content = "";
        if ($this->is_radio) {
            $content = LocalizationHelper::translate('notifications.types.channels_new_recording.text_1_radio') . ' ' . $this->data->channel->name . ' ' . LocalizationHelper::translate('notifications.types.channels_new_recording.text_2_radio') . PHP_EOL . PHP_EOL . ($this->data->title) . PHP_EOL . PHP_EOL . $url;
        } else {
            $content = LocalizationHelper::translate('notifications.types.channels_new_recording.text_1_tv') . ' ' . $this->data->channel->name . ' ' . LocalizationHelper::translate('notifications.types.channels_new_recording.text_2_tv') . PHP_EOL . PHP_EOL . ($this->data->title) . PHP_EOL . PHP_EOL . $url;
        }
        $message = (new VKMessage())->content($content);
        if ($this->data->thumbnail) {
            $message->picture($this->data->thumbnail);
        }
        return $message;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notification_type' => 'ChannelNewRecording',
            'title' => ($this->is_radio ? LocalizationHelper::translate('notifications.types.channels_new_recording.heading_radio') : LocalizationHelper::translate('notifications.types.channels_new_recording.heading_tv')).$this->data->channel->name,
            'picture' => $this->data->channel->logo,
            'text' => $this->data->title,
            'translate' => [],
            'notification_link' => $this->getRelativeURL(),
            'notification_data' => $this->data,
        ]);
    }




    public function toDatabase($notifiable)
    {
        return [
            'is_radio' => $this->is_radio,
            'channel' => [
                'id' => $this->data->channel->id,
                'shortname' => $this->data->channel->shortname,
                'name' => $this->data->channel->name,
                'logo' => $this->data->channel->logo,
            ],
            'data' => [
                'id' => $this->data->id,
                'title' => $this->data->title,
                'time' => $this->is_radio ? $this->data->created_at->timestamp : $this->data->add_time,
            ]
        ];
    }
}
