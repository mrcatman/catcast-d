<?php
namespace App\Helpers;
class LocalizationHelper {

    public static function getCurrentLanguage() {
        return 'ru'; //todo
    }

    public static function getTexts() {
        // todo: split by languages
        return json_decode(file_get_contents(public_path('locales.json')), 1);
    }

    public static function getTextsForCurrentLanguage() {
        return self::getTexts()['messages'][self::getCurrentLanguage()];
    }

    public static function translate($text_path, $args = []) {
        return [
            'text' => $text_path,
            'params' => $args
        ];
        // todo: check context, translate in mail, return array in web
        $texts = self::getTextsForCurrentLanguage();

        $text_path_parts = explode('.',$text_path);

        if (!isset($texts[$text_path_parts[0]])) {
            return $text_path;
        }
        $translated = $texts[$text_path_parts[0]];
        for ($i = 1; $i < count($text_path_parts); $i++) {
            $translated = $translated[$text_path_parts[$i]] ?? null;
        }
        if ($translated != null && $translated !== '') {
            return self::replaceArgs($translated, $args);
        } else {
            return $text_path;
        }
    }

    public static function replaceArgs($text, $args) {
        foreach ($args as $arg => $value) {
            $text = str_replace('{'.$arg.'}', $value, $text);
        }
        return $text;
    }

    public static function getFormErrors($text_path) {
        $data = self::translate($text_path);
        $texts = [];

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $error => $error_text) {
                    $texts[$key.".".$error] = $text_path.".".$key.".".$error;
                }
            } else {
                $texts[$key] = $text_path . "." . $key;
            }
        }
        return $texts;
    }

}
