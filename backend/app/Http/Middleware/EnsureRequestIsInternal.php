<?php

namespace App\Http\Middleware;

use App\Helpers\ServersHelper;
use Closure;

/**
 * Restricts /api/internal to the streaming servers (see streaming/ in the
 * repository root), by matching the caller against the callback_sources of
 * each entry in config/servers.php.
 *
 * This used to ask whether the caller sat on a private network, which says
 * nothing about whether it is one of ours -- and, as written, the test was
 * inverted: every public address passed it while loopback and most private
 * ranges were rejected. Matching known addresses is both the right question
 * and one that still works when the servers are in different locations.
 *
 * The server the caller was recognised as is put on the request, so a
 * controller identifies it by where the request came from rather than by what
 * it claimed in a parameter.
 *
 * This is only as good as $request->ip(). That returns REMOTE_ADDR while
 * TrustProxies trusts no proxies, which is the case today. If a proxy is ever
 * trusted there, X-Forwarded-For becomes caller-controlled and this check with
 * it, so the two have to be changed together.
 */
class EnsureRequestIsInternal {

    public function handle($request, Closure $next) {
        $server_id = ServersHelper::idFromAddress($request->ip());

        if ($server_id === null) {
            abort(403, 'Access denied');
        }

        $request->attributes->set(ServersHelper::REQUEST_ATTRIBUTE, $server_id);
        return $next($request);
    }

}
