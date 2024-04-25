<?php
namespace App\Bots;
use App\ExternalServices\ExternalServiceConnection;
use App\Helpers\LocalizationHelper;

class Bot {
    protected $connection = ExternalServiceConnection::class;
    protected $handler = BotApiHandler::class;
    protected $update = BotUpdate::class;

    protected $keyboard_lang = [
        'text'=>'Сменить язык / Set language',
        'command'=>'set_language',
        'lang'=>false
    ];

    protected $keyboard_bind = [
        'text'=>'bot.commands.bind_account.heading',
        'command'=>'bind_account',
        'lang'=>true
    ];

    public function handle($data)
    {
        $update = new $this->update($data);
        $connection = new $this->connection();
        $handler = new $this->handler();
        if ($update->isValid()) {
            $chat_id = $update->getChatId();
            $connection->createByExternalHandle($chat_id);
            $handler->setChatId($chat_id);
            $need_send = true;
            $this->lang->setLanguage($connection->getLanguage());
            $handler->setLanguage($connection->getLanguage());
            // if (!$connection->isConfirmed()) {
            $default_keyboard = [$this->keyboard_lang, $this->keyboard_bind];
            //} else {
            //$default_keyboard = [$this->keyboard_lang, $this->keyboard_bind];
            //}

            if ($update->isCommand()) {
                $need_send = false;
                $command_name = $update->getCommandName();
                $message_id = $update->getCommandMessageId();
                if ($command_name === "set_language") {
                    $message = "Выберите язык / Select language";
                    $keyboard = $this->makeLanguagesKeyboard();
                    $handler->setMessage($message)->setKeyboard($keyboard);
                    if ($handler->supportsEditing()) {
                        $handler->setMessageId($message_id);
                        $handler->edit();
                    } else {
                        $handler->send();
                    }
                } elseif ($command_name === "select_language") {
                    $language_code = $update->getCommandParams()[0];
                    if ($language_code) {
                        $connection->setLanguage($language_code);
                        $this->lang->setLanguage($language_code);
                        $handler->setLanguage($language_code);
                        $message = htmlspecialchars(LocalizationHelper::translate("bot.commands.set_language.after_setting") . " " . LocalizationHelper::translate("_language_name"));
                        $handler->setMessage($message)->setKeyboard($default_keyboard)->send();
                    }
                } elseif ($command_name === "bind_account") {
                    if ($connection->isNotConfirmed() || $connection->canResendConfirmation()) {
                        $link = $connection->startConfirmation();
                        $message = LocalizationHelper::translate("bot.commands.bind_account.go_to_link") . " " . $link;
                        $handler->setMessage($message)->send();
                    } elseif ($connection->isWaitingForConfirmation()) {
                        $message = LocalizationHelper::translate("bot.commands.bind_account.link_already_sent");
                        $handler->setMessage($message)->send();
                    }
                }
            }
            if ($need_send) {
                if ($connection->isFirstTime()) {
                    $message = "Бот сервиса Catcast.tv приветствует вас! ".PHP_EOL."После привязки аккаунта сайта бот сможет отправлять вам уведомления о событиях на сайте.";
                    $handler->setMessage($message)->setKeyboard($default_keyboard)->send();
                } else {

                }
            }
        } else {
            file_put_contents(public_path("msg.txt"),json_encode($data->message));
        }
    }

    public function makeLanguagesKeyboard() {
        $keyboard = [];
        foreach ($this->lang->texts as $language_code => $language_data) {
            $keyboard[] = [
                'text' => $language_data['_language_name'],
                'command' => "select_language.".$language_code,
                'lang' => false,
            ];
        }
        return $keyboard;
    }
}
