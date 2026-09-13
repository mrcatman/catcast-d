<?php

namespace App\Helpers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RelationsHelper {

    public static function fillIfExists($entity, $field, $class, $check_channel_id = false, $value = null) {
        if (!$value) {
            $value = request()->input($field);
        }
        if ($value > 0) {
            $instance = $class::find($value);
            if ($instance) {
                if ($check_channel_id && $instance->channel_id != $entity->channel_id) {
                    return;
                }
                $entity->{$field} = $instance->id;
            } else {
                $messages = [];
                $messages[$field] = ['errors.item_not_found'];
                $error = \Illuminate\Validation\ValidationException::withMessages($messages);
                throw $error;
            }
        }
    }
}
