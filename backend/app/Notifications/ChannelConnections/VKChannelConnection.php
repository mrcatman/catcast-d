<?php

namespace App\Notifications\ChannelConnections;

class VKChannelConnection extends NotificationChannelConnection {

    public static function getDisplayName(): string {
        return 'notifications.channels.vk';
    }

    public static function getId(): string {
        return 'vk';
    }

    public static function canSubscribe($user): bool {
        return $user->accountConnections()->where(['account_type' => 'vk', 'confirmed' => true])->count() > 0;
    }

    public static function enabled(): bool {
        return config('site.bot_public_urls.vk') != '';
    }

}
