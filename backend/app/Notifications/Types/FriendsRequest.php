<?php

namespace App\Notifications\Types;

use App\Models\Channel;
use App\Models\User;
use App\Models\UserChannelPermissions;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\LocalizationHelper;

use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\Telegram\TelegramMessage;
use App\Notifications\Channels\VK\Message as VKMessage;


class FriendsRequest extends BaseNotificationType {

    protected $lang;

    protected $default_channels = [];

    protected $data;

    protected $me;

    public function __construct($user = null, $me = null) {
        $this->data = $user;
        $this->me = $me;
        parent::__construct($user);
    }

    public static function getEventTypeId() {
        return 'friends_request';
    }

    public static function getDisplayName() {
        return 'notifications.types.users.subtypes.friends_request.title';
    }

    public function getEntity() {
        return $this->user;
    }



    public function getLink() {
        return "/users/".$this->me->id."?t=friends&friends=requests";
    }



    public function toTelegram($notifiable) {
        $url = $this->getFullURL();
        $content = LocalizationHelper::translate('notifications.texts.friends_request.full_title')."*".($this->data->username)."*";
        return TelegramMessage::create()
            ->to($notifiable->telegram_id)
            ->content($content)
            ->button(LocalizationHelper::translate("notifications.texts.friends_request._button_text"), $url);
    }

    public function toVK() {
        $url = $this->getFullURL();
        $content = LocalizationHelper::translate('notifications.texts.friends_request.full_title').$this->data->username.PHP_EOL.PHP_EOL.$url;
        return (new VKMessage())->content($content);
    }


    public function toMail($notifiable)
    {
        $common_channels = $this->getCommonChannels();
        $common_friends = $this->getCommonFriends();
        return (new MailMessage)->subject(LocalizationHelper::translate('notifications.texts.friends_request.full_title').$this->data->username)->view('emails.friends_request', [
            'data' => $this->data,
            'url' => $this->getFullURL(),
            'lang' => $this->lang,
            'common_channels' => $common_channels,
            'common_friends' => $common_friends,
        ]);
    }

    public function getCommonFriends() {
        $common = $this->data->friends($this->data->id)->pluck('id');
        $users = User::whereIn('id', $common)->pluck('username')->toArray();
        return $users;
    }


    public function getCommonChannels() {
        $channels_author = Channel::where(['user_id' => $this->data->id])->pluck('id')->toArray();
        $channels_work = UserChannelPermissions::where(['user_id'=>$this->data->id])->pluck('channel_id')->toArray();
        $my_channels_author = Channel::where(['user_id' => $this->me->id])->pluck('id')->toArray();
        $my_channels_work = UserChannelPermissions::where(['user_id'=>$this->me->id])->pluck('channel_id')->toArray();
        $common_ids = array_intersect(array_merge($channels_author, $channels_work), array_merge($my_channels_author, $my_channels_work));
        $channel_names = Channel::whereIn('id', $common_ids)->pluck('name')->toArray();
        return $channel_names;
    }


    public function toBroadcast($notifiable) {
      return new BroadcastMessage([
          'notification_type' => 'FriendsRequest',
          'title' => 'notifications.texts.friends_request.title',
          'picture' => $this->data->avatar,
          'text' => $this->data->username,
          'notification_link' => $this->getRelativeURL()
          'translate' => ['title']
      ]);
    }
}
