<?php

namespace App\Bots\Telegram;
use App\Bots\BotApiHandler;

use App\Helpers\LocalizationHelper;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramBotApiHandler extends BotApiHandler
{
    public function supportsEditing() {
        return true;
    }

    public function send() {
        $data = [
            'chat_id'=> $this->chat_id,
            'text' => htmlspecialchars($this->message),
            'parse_mode' => 'HTML'
        ];
        if ($this->keyboard) {
            $data['reply_markup'] = $this->makeKeyboard($this->keyboard);
        }
        Telegram::sendMessage($data);
    }

    public function edit() {
        $data = [
            'chat_id'=> $this->chat_id,
            'message_id' => $this->message_id,
            'text' => htmlspecialchars($this->message),
            'parse_mode' => 'HTML'
        ];
        if ($this->keyboard) {
            $data['reply_markup'] = $this->makeKeyboard($this->keyboard);
        }
        Telegram::editMessageText($data);
    }

    private function makeKeyboard($keyboard_items) {
        $lang = new LocalizationHelper();
        if ($this->language_code) {
            $lang->setLanguage($this->language_code);
        }
        $keyboard = Keyboard::make()->inline();
        foreach ($keyboard_items as $item) {
            if ($item['lang']) {
                $text = LocalizationHelper::translate($item['text']);
            } else {
                $text = $item['text'];
            }
            $button = Keyboard::inlineButton(['text' => $text, 'callback_data' => $item['command']]);
            $keyboard->row($button);
        }
        return $keyboard;
    }
}
