<?php

namespace App\Http\Middleware;

use Closure;

class EnsureRequestIsInternal {

    public function handle($request, Closure $next) {
        if ($request->ip() != '127.0.0.1') {
            abort(403, 'Access denied');
        }
        return $next($request);
    }

}
