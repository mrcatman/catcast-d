<?php

namespace App\Bots\VK;
use App\Bots\BotApiHandler;
use App\Helpers\LocalizationHelper;

class VKBotApiHandler extends BotApiHandler
{
    protected $token;
    public function __construct() {
        $this->token = config('services.vkontakte.token');
    }


    public function supportsEditing() {
        return false;
    }

    public function send() {
        $request_params = array(
            'message' => $this->message,
            'user_id' => $this->chat_id,
            'access_token' => $this->token,
            'v' => '5.87'
        );

        if ($this->keyboard) {
            $request_params['keyboard'] = json_encode($this->makeKeyboard($this->keyboard),JSON_UNESCAPED_UNICODE);
        }
        $ch = curl_init( 'https://api.vk.com/method/messages.send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($request_params) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $response = curl_exec($ch);
        curl_close($ch);
        //file_put_contents(public_path("vk_bot_response.txt"),json_encode([
        //    'params'=>$request_params,
        //    'response'=>$response,
        //]));
    }

    public function edit() {

    }

    private function makeKeyboard($keyboard_items) {
        $buttons = [];
        $lang = new LocalizationHelper();
        if ($this->language_code) {
            $lang->setLanguage($this->language_code);
        }
        $keyboard = ['buttons'=>[],'one_time'=>false];
        foreach ($keyboard_items as $item) {
            if ($item['lang']) {
                $text = LocalizationHelper::translate($item['text']);
            } else {
                $text = $item['text'];
            }
            $buttons[] = [
                'action' => [
                    'type'=>'text',
                    'payload'=>json_encode(['command'=>$item['command']]),
                    'label'=>$text,
                ],
                "color"=>"default"
            ];
        }
        $keyboard['buttons'][] = $buttons;
        return $keyboard;
    }
}
