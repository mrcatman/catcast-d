<?php
namespace App\Notifications\Types;

use App\Helpers\ConfigHelper;
use App\Models\Channel;
use App\Models\NotificationBinding;
use App\Notifications\Channels\DatabaseExtendedChannel;
use App\Models\NotificationSubscription;
use App\Models\User;
use App\Notifications\Channels\VK\Message as VKMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Notification;


use Illuminate\Support\Collection;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use App\Notifications\Channels\VKChannel;

class BaseNotificationType extends Notification {

    //use Queueable;

    protected $via = [];
    protected $default_channels = [];

    /**
     * Get internal name of the notification type
     *
     * @return string
     */
    public static function getEventTypeId() {
        return '';
    }


    /**
     * Get entity connected with the notification
     *
     * @return Model
     */
    public function getEntity() {
        return null;
    }

    /**
     * Get model class connected with the notification
     *
     * @return string
     */
    public static function getEntityClass() {
        return '';
    }

    /**
     * Set list of notification channels by which we should send notifications
     *
     * @param string[] $via list of channels
     * @void
     */
    public function setVia(array $via) {
        if (!$via) {
            $via = [];
        }
        $via = array_merge($via, $this->default_channels);
        $channels = [];
        foreach ($via as $item) {
            if ($item == "telegram") {
                $channels[] = TelegramChannel::class;
            } elseif ($item == "vk") {
                $channels[] = VKChannel::class;
            } elseif ($item == 'broadcast') {
                /* Broadcast the notification and also put it in the database */
                $channels[] = BroadcastChannel::class;
                $channels[] = DatabaseExtendedChannel::class;
            } else {
                $channels[] = $item;
            }
        }
        $this->via = $channels;
    }


    /**
     * Get title for emails, on-site notifications list, etc.
     *
     * @return string
     */
    public function getTitle() {
        return '';
    }

    /**
     * Get full URL by which user will go on clicking on the notification, usually the link on channel's page
     *
     * @return string
     */
    public function getRelativeURL() {
        return '';
    }

    /**
     * Get full URL by which user will go on clicking on the notification
     *
     * @return string
     */
    public function getFullURL() {
        return ConfigHelper::siteURL().$this->getLink();
    }


    /**
     * Exclude specific users from receiving a notification (e.g. the author of a post)
     * @param Collection $users
     *
     * @return Collection
     */
    public function filterUsers(Collection $users) {
        return $users;
    }

    /**
     * Send the notification to people subscribed to a channel
     * @param Channel $channel
     *
     * @void
     */
    public function sendToChannelSubscribers(Channel $channel) {
        $subscriptions = NotificationSubscription::where([
            'event_type' => self::getEventTypeId(),
            'entity_id' => $channel->id
        ])->get();

        $bindings = NotificationBinding::where([
            'event_type' => $this->getEventTypeId()
        ])->get();

        $bindings_list = [];
        foreach ($bindings as $binding) {
            if (!isset($bindings_list[$binding->user_id])) {
                $bindings_list[$binding->user_id] = [];
            }
            $bindings_list[$binding->user_id][] = $binding->notification_channel_type;
        }

        $users = collect([]);
        foreach ($subscriptions as $subscription) {
            if (isset($bindings_list[$subscription->user_id])) {
                $users->push([
                    'channels' => $bindings_list[$subscription->user_id],
                    'user' => $subscription->user
                ]);
            }
        }
        $users = $this->filterUsers($users);

        foreach ($users as $user_data) {
            $this->setVia($user_data['channels']);
            $user_data['user']->notify($this);
        }
    }

    /**
     * Send the notification to a specific user
     * @param User $user
     *
     * @void
     */
    public function sendToUser(User $user) {
        $bindings = NotificationBinding::where([
            'type' => self::getEventTypeId(),
            'user_id' => $user->id
        ])->get();

        /* Notify user by default channels */
        if (count($bindings) == 0) {
            $this->setVia([]);
            $user->notify($this);
            return;
        }
        foreach ($bindings as $binding) {
            $this->setVia([$binding->notification_channel_type]);
            $binding->user->notify($this);
        }
    }


    public function toTelegram($notifiable) {
        $full_url = $this->getFullURL();
        $content = $this->getTitle();
        return TelegramMessage::create()
            ->to($notifiable->telegram_id)
            ->content($content)
            ->button(self::getButtonText(), $full_url);
    }

    public function toVK() {
        $full_url = $this->getFullURL();
        $content = $this->getTitle().PHP_EOL.$full_url;
        return (new VKMessage())->content($content);
    }

    public function toDatabase($notifiable) {
        return $this->toBroadcast($notifiable);
    }

    public static function getStyles() {
        return ''; // todo: email styles
        //return Storage::disk('public_uploads')->get('css/emails.css');
    }
}
