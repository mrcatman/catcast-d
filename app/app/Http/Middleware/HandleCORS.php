<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCORS
{

    public function handle(Request $request, Closure $next) {
        $response = $next($request);
        if (app()->environment('local')) {
            $referer = request()->headers->get('referer');

            if ($referer) {
                $parsed = parse_url($referer);
                $host = $parsed['host'];
                if (str_starts_with($host, 'localhost')) {
                    if (isset($parsed['port'])) {
                        $host = $host . ":" . $parsed['port'];
                    }
                    $response->header('Access-Control-Allow-Origin', "http://" . $host)
                        ->header('Access-Control-Allow-Credentials', 'true')
                        ->header('Access-Control-Allow-Methods', 'PUT,POST,DELETE,GET,HEAD,OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'X-Timezone-Offset,Accept,Authorization,Content-Type,X-XSRF-Token');
                }
            }
        }
        return $response;
    }

}
