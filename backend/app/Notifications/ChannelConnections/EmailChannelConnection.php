<?php
namespace App\Notifications\ChannelConnections;

class EmailChannelConnection extends NotificationChannelConnection {

    public static function getDisplayName(): string {
        return 'notifications.channels.email';
    }

    public static function getId(): string {
        return 'mail';
    }

    public static function canSubscribe($user): bool {
        return true;
    }

    public static function enabled(): bool {
        return true;
    }

}
