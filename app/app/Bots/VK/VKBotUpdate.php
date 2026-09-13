<?php
namespace App\Bots\VK;
use App\Bots\BotUpdate;
class VKBotUpdate extends BotUpdate {

    public function getChatId()
    {
        return $this->data['object']['from_id'];
        //return $chat_id;
    }

    public function isCommand()
    {
         return isset($this->data['object']['payload']);
    }

    public function getCommandMessageId() {
        return $this->data['object']['conversation_message_id'];
    }

    public function getCommandName()
    {
        $payload = json_decode($this->data['object']['payload']);
        $command_data = explode(".",$payload->command);
        $command_name = $command_data[0];
        return $command_name;
    }

    public function getCommandParams()
    {
        $payload = json_decode($this->data['object']['payload']);
        $command_data = explode(".",$payload->command);
        array_shift($command_data);
        return $command_data;
    }
}