<?php

namespace App\Helpers;

class CommonResponses {

    public static function validationError($data) {
        $response = [
            'errors' => $data
        ];
        $message = array_values($data)[0];
        if (is_array($message)) {
            $message = $message[0];
        }
        $response['message'] = $message;
        return response()->json($response, 422);
    }

    public static function unauthorized() {
        return response()->json(['message' => 'errors.unauthorized'], 403);
    }

    public static function notFound() {
        return response()->json(['message' => 'errors.item_not_found'], 404);
    }

}
