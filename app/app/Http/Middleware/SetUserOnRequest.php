<?php

namespace App\Http\Middleware;


use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Token;

class SetUserOnRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

//        $offset = (int)request()->header('X-Timezone-Offset'); // todo: move
//        if (!$offset) {
//            date_default_timezone_set('UTC');
//        } else {
//            $timezone_name = timezone_name_from_abbr('', $offset * -60, false);
//            if ($timezone_name && $timezone_name != "") {
//                date_default_timezone_set($timezone_name);
//            }
//        }
        if (request()->cookie('token')) {
            try {
                $token = new Token(request()->cookie('token'));
                $payload = JWTAuth::decode($token);
                $user = User::find($payload['sub']);
                if ($user) {
                    JWTAuth::fromUser($user);
                    $user->last_seen = Carbon::now();
                    $user->last_ip_address = request()->ip();
                    $user->save();
                }
            } catch (\Exception $e) {

            }
        }

        return $next($request);
    }
}
