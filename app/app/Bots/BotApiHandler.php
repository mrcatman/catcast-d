<?php

namespace App\Bots;

class BotApiHandler {
    protected $message;
    protected $chat_id;
    protected $keyboard;
    protected $message_id;
    protected $language_code;

    public function supportsEditing() {
        return false;
    }

    public function setLanguage($language_code) {
        $this->language_code = $language_code;
    }
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    public function setKeyboard($keyboard) {
        $this->keyboard = $keyboard;
        return $this;
    }

    public function setChatId($chat_id) {
        $this->chat_id = $chat_id;
        return $this;
    }

    public function setMessageId($message_id) {
        $this->message_id = $message_id;
        return $this;
    }

    public function send() {

    }

    public function edit() {

    }
}