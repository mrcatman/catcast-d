<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class EnsureRequestIsInternal {

    public function handle($request, Closure $next) {
        if (
            !filter_var($request->ip(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
            && !Str::startsWith($request->ip(), '172.') // todo: find a normal way to check for local IPs
        ) {
            abort(403, 'Access denied');
        }
        return $next($request);
    }

}
