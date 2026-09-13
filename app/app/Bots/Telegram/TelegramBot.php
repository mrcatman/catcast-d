<?php

namespace App\Bots\Telegram;
use App\Bots\Bot;
use App\ExternalServiceConnections\TelegramExternalServiceConnection;

class TelegramBot extends Bot
{
    protected $connection = TelegramExternalServiceConnection::class;
    protected $handler = TelegramBotApiHandler::class;
    protected $update = TelegramBotUpdate::class;
}