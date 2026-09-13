<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Resolves the streaming server ids stored on broadcasts and media into the
 * addresses defined in config/servers.php.
 *
 * Every lookup falls back to the default server: an id that no longer appears
 * in the config should degrade to "play it from the default host", not fatal
 * halfway through rendering a page.
 */
class ServersHelper {

    // Where EnsureRequestIsInternal records the server it recognised the caller
    // as. Set on the request rather than read from a parameter, so a caller
    // cannot name itself.
    const REQUEST_ATTRIBUTE = 'streaming_server_id';

    // Long enough to keep DNS off the request path, short enough that a
    // recreated container is picked up without flushing the cache.
    const SOURCE_CACHE_SECONDS = 60;

    public static function all() {
        return config('servers', []);
    }

    public static function exists($id) {
        return is_string($id) && $id !== '' && array_key_exists($id, self::all());
    }

    public static function defaultId() {
        foreach (self::all() as $id => $server) {
            if (!empty($server['default'])) {
                return $id;
            }
        }
        return array_key_first(self::all());
    }

    /**
     * Configuration of one server, by id. Unknown or missing ids resolve to
     * the default server.
     * @param string|null $id
     * @return array
     */
    public static function get($id = null) {
        $servers = self::all();
        if (!self::exists($id)) {
            $id = self::defaultId();
        }
        return $servers[$id] ?? [];
    }

    /**
     * The server whose token authenticated the current request, as resolved by
     * EnsureRequestIsInternal. Only meaningful on the /api/internal routes that
     * middleware guards.
     * @return string
     */
    public static function idFromRequest() {
        $id = request()->attributes->get(self::REQUEST_ATTRIBUTE);
        return self::exists($id) ? $id : self::defaultId();
    }

    /**
     * Id of the server whose configured callback_sources cover this address, or
     * null if none does. Every server is checked rather than returning on the
     * first hit, so an overlapping configuration is at least deterministic.
     *
     * Hostnames are resolved and cached briefly: container addresses change
     * when the stack is recreated, and a DNS lookup per callback would sit on
     * the request path.
     * @param string|null $address
     * @return string|null
     */
    public static function idFromAddress($address) {
        if (!is_string($address) || $address === '') {
            return null;
        }
        $match = null;
        foreach (self::all() as $id => $server) {
            foreach ($server['callback_sources'] ?? [] as $source) {
                $ranges = self::resolveSource($source);
                if ($ranges && IpUtils::checkIp($address, $ranges)) {
                    $match = $id;
                }
            }
        }
        return $match;
    }

    /**
     * One configured source as a list of addresses or CIDRs. Anything that is
     * not already an address or a range is treated as a hostname and resolved.
     * @param string $source
     * @return array
     */
    private static function resolveSource($source) {
        if (Str::contains($source, '/') || filter_var($source, FILTER_VALIDATE_IP)) {
            return [$source];
        }
        return Cache::remember('streaming-server-source:'.$source, self::SOURCE_CACHE_SECONDS, function() use ($source) {
            return gethostbynamel($source) ?: [];
        });
    }

    /**
     * Origin for application to server calls: the rtmp control module.
     */
    public static function internalHttpUrl($id = null) {
        $server = self::get($id);
        return 'http://'.($server['host'] ?? 'streaming');
    }

    /**
     * Origin for application to server RTMP pulls: the thumbnail grabber.
     */
    public static function internalRtmpUrl($id = null) {
        $server = self::get($id);
        return 'rtmp://'.($server['host'] ?? 'streaming').':'.($server['rtmp_port'] ?? 1935);
    }

    /**
     * Origin viewer-facing HLS and VOD URLs are built against.
     */
    public static function publicHost($id = null) {
        $server = self::get($id);
        return $server['public_host'] ?? ConfigHelper::siteURL();
    }

    /**
     * tus endpoint uploads for this server are sent to. Same origin as playback
     * -- it is the one public address the server has -- but kept separate so
     * the two can diverge without hunting down string concatenation.
     */
    public static function uploadEndpoint($id = null) {
        return rtrim(self::publicHost($id), '/').'/api/files';
    }

    /**
     * RTMP ingest address handed to broadcasters.
     */
    public static function publicRtmpUrl($id = null) {
        $server = self::get($id);
        return $server['public_rtmp_url'] ?? ConfigHelper::rtmpURL();
    }

}
