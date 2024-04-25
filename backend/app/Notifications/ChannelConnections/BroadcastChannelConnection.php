<?php

namespace App\Notifications\ChannelConnections;

class BroadcastChannelConnection extends NotificationChannelConnection {

    public static function getDisplayName(): string {
        return 'notifications.channels.broadcast';
    }

    public static function getId(): string {
        return 'broadcast';
    }

    public static function canSubscribe($user): bool {
        return true;
    }

    public static function enabled(): bool {
        return true;
    }

}
