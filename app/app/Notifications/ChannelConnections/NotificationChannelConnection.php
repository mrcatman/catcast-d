<?php
namespace App\Notifications\ChannelConnections;

use App\Models\User;

class NotificationChannelConnection {

    public static function getDisplayName(): string {
        return '';
    }

    public static function getId(): string {
        return '';
    }

    public static function canSubscribe(User $user): bool {
        return false;
    }

    public static function enabled(): bool {
        return false;
    }

}
