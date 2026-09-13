<?php

namespace App\Notifications\ChannelConnections;

class TelegramChannelConnection extends NotificationChannelConnection {

    public static function getDisplayName(): string {
        return 'notifications.channels.telegram';
    }

    public static function getId(): string {
        return 'telegram';
    }

    public static function canSubscribe($user): bool {
        return $user->accountConnections()->where(['account_type' => 'telegram', 'confirmed' => true])->count() > 0;
    }

    public static function enabled(): bool {
        return config('site.bot_public_urls.telegram') != '';
    }
}
