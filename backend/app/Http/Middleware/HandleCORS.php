<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCORS
{

    public function handle(Request $request, Closure $next) {
        $response = $next($request);
        $referer = request()->headers->get('referer');
        $is_tus = strpos(request()->fullUrl(), '/tus') !== false;
        if ($is_tus) {
            $response->headers->set('Access-Control-Allow-Origin', "*");
            return $response;
        }
        if ($referer) {
            $parsed =  parse_url($referer);
            $host = $parsed['host'];

            if (isset($parsed['port'])) {
                $host = $host.":".$parsed['port'];
            }
            $protocol = strpos($host, 'localhost') !== false ? 'http' : 'https';
            $response->header('Access-Control-Allow-Origin', $protocol . "://" . $host);
            $response->header('Access-Control-Allow-Credentials', 'true');
        } else {
           $response->header('Access-Control-Allow-Origin', "*");
        }
        $response->header('Access-Control-Allow-Methods',"PUT,POST,DELETE,GET,HEAD,OPTIONS")
            ->header('Access-Control-Allow-Headers',"X-Timezone-Offset,Accept,Authorization,Content-Type,X-XSRF-Token")
            ->header('Access-Control-Expose-Headers', 'Location');
        return $response;
    }

}
