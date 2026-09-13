<?php
namespace App\Bots;
class BotUpdate {
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getChatId() {
        return null;
    }

    public function isCommand() {
        return null;
    }

    public function getCommandMessageId() {
        return null;
    }

    public function getCommandName() {
        return null;
    }

    public function getCommandParams() {
        return null;
    }
}