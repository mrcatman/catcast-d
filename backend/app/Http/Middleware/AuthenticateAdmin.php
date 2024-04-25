<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateAdmin extends Middleware
{

    protected function redirectTo($request)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json([
                'message' => '403'
            ], 403);
        }
    }
}
