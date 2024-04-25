<?php
namespace App\Bots\Telegram;
use App\Bots\BotUpdate;
class TelegramBotUpdate extends BotUpdate {

    public function getChatId() {
        $chat_id = null;
        if (isset($this->data->callback_query)) {
            $chat_id = $this->data->callback_query->message->chat->id;
        } else {
            if(isset($this->data->message)) {
                $chat_id = $this->data->message->chat->id;
            }
        }
        return $chat_id;
    }

    public function isValid() {
        return isset($this->data->callback_query) || isset($this->data->message);
    }

    public function isCommand()
    {
        return isset($this->data->callback_query);
    }

    public function getCommandMessageId() {
        return $this->data->callback_query->message->message_id;
    }

    public function getCommandName()
    {
        $command_data = explode(".",$this->data->callback_query->data);
        $command_name = $command_data[0];
        return $command_name;
    }

    public function getCommandParams()
    {
        $command_data = explode(".",$this->data->callback_query->data);
        array_shift($command_data);
        return $command_data;
    }
}