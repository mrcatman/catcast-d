<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\AccountConnection;
use App\Helpers\LocalizationHelper;
class TelegramBotController extends Controller{

    protected $found_account = false;
    protected $keyboard_lang =
        [
            'name'=>'Сменить язык / Set language',
            'command'=>'set_language',
            'lang'=>false,
        ];

    protected $keyboard_bind =
        [
            'name'=>'bot.commands.bind_account.heading',
            'command'=>'bind_account',
            'lang'=>true,
        ];

    protected $lang;


    public function handle($update) {
        if (isset($update->callback_query)) {
            $chat_id = $update->callback_query->message->chat->id;
        } else {
            $chat_id = $update->message->chat->id;
        }
        $connection = AccountConnection::where(['account_type'=>'telegram'])->where(['account_name'=>$chat_id])->first();
        $first_time_chat = !$connection;
        $need_send = true;
        if (!$connection) {
            $connection = new AccountConnection([
                'account_type'=>'telegram',
                'account_name'=>$chat_id,
                'confirm_status'=>-1
            ]);
            $connection->save();
        } else {
            if ($connection->language_code) {
                $this->lang->setLanguage($connection->language_code);
            }
        }
        $default_keyboard = [$this->keyboard_lang, $this->keyboard_bind];
        if (isset($update->callback_query)) {
            $need_send = false;
            $chat_id = $update->callback_query->message->chat->id;
            $command_data = explode(".",$update->callback_query->data);
            $command_name = $command_data[0];
            $message_id = $update->callback_query->message->message_id;
            if ($command_name === "set_language") {
                $message = "Выберите язык / Select language";
                $keyboard = $this->makeLanguagesKeyboard();
                Telegram::editMessageText([
                    'chat_id'=> $chat_id,
                    'message_id' => $message_id,
                    'text' => htmlspecialchars($message),
                    'reply_markup' => $keyboard,
                    'parse_mode' => 'HTML'
                ]);
            } elseif ($command_name === "select_language") {
                $language_code = $command_data[1];
                if ($language_code) {
                    $connection->language_code = $language_code;
                    $connection->save();
                    $this->lang->setLanguage($language_code);
                    $keyboard = $this->makeKeyboard($default_keyboard);
                    Telegram::editMessageText([
                        'chat_id'=> $chat_id,
                        'message_id' => $message_id,
                        'text' => htmlspecialchars(LocalizationHelper::translate("bot.commands.set_language.after_setting")." ".LocalizationHelper::translate("_language_name")),
                        'reply_markup' => $keyboard,
                        'parse_mode' => 'HTML'
                    ]);
                }
            } elseif ($command_name === "bind_account") {
                if ($connection->confirm_status == -1 || (time() - $connection->updated_at->timestamp > 3600)) {
                    $code = str_random(30);
                    $link = env("CLIENT_HOST")."confirm-account?code=$code";
                    $message = LocalizationHelper::translate("bot.commands.bind_account.go_to_link")." ".$link;
                    Telegram::sendMessage([
                        'chat_id' => $chat_id,
                        'text' => htmlspecialchars($message),
                        'parse_mode' => 'HTML'
                    ]);
                    $connection->confirm_code = $code;
                    $connection->confirm_status = 0;
                    $connection->save();
                } elseif ($connection->confirm_status == 0) {
                    $message = LocalizationHelper::translate("bot.commands.bind_account.link_already_sent");
                    Telegram::sendMessage([
                        'chat_id' => $chat_id,
                        'text' => htmlspecialchars($message),
                        'parse_mode' => 'HTML'
                    ]);
                }
            }
        }
        if ($need_send) {
           if ($first_time_chat) {
               $message = "Добро пожаловать в бота для сервиса MyOwnTV! <br> <br> Welcome to MyOwnTV service bot!";
           } else {
               $message = "...";
           }
           $keyboard = $this->makeKeyboard($default_keyboard);
           Telegram::sendMessage([
               'chat_id' => $chat_id,
               'text' => htmlspecialchars($message),
               'reply_markup' => $default_keyboard,
               'parse_mode' => 'HTML'
           ]);
        }
    }

    public function makeKeyboard($keyboard_items) {
        $keyboard = Keyboard::make()->inline();
        foreach ($keyboard_items as $item) {
            if ($item['lang']) {
                $text = LocalizationHelper::translate($item['name']);
            } else {
                $text = $item['name'];
            }
            $button = Keyboard::inlineButton(['text' => $text, 'callback_data' => $item['command']]);
            $keyboard->row($button);
        }
        return $keyboard;
    }

    public function makeLanguagesKeyboard() {
        $keyboard = Keyboard::make()->inline();
        foreach ($this->lang->texts as $language_code => $language_data) {
            $button = Keyboard::inlineButton(['text' => $language_data['_language_name'], 'callback_data' => "select_language.".$language_code]);
            $keyboard->row($button);
        }
        return $keyboard;
    }
}
