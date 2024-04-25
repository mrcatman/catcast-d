<?php

namespace App\Bots\VK;
use App\Bots\Bot;
use App\ExternalServiceConnections\VKExternalServiceConnection;

class VKBot extends Bot
{
    protected $connection = VKExternalServiceConnection::class;
    protected $handler = VKBotApiHandler::class;
    protected $update = VKBotUpdate::class;
}