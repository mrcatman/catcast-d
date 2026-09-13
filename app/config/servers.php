<?php

/*
|--------------------------------------------------------------------------
| Streaming servers
|--------------------------------------------------------------------------
|
| Each entry is one streaming edge -- the nginx-rtmp/vod container built from
| streaming/ in the repository root -- keyed by its internal id. Broadcasts and
| media store that id, so a record always resolves back to the machine holding
| its segments and recordings.
|
| Adding a server: copy streaming/ to the new host, add the entry here, and
| list the addresses its callbacks arrive from. The ids end up in the database,
| so renaming one orphans everything already recorded against it.
|
| 'callback_sources' is the whole of the authentication on /api/internal (see
| App\Http\Middleware\EnsureRequestIsInternal): a callback is accepted if it
| came from one of these addresses, and the server it identifies is the server
| the request is attributed to. Matching on address rather than on a private
| range is what lets the servers sit in different locations.
|
*/

return [

    'default' => [

        // The server the application picks when nothing else says otherwise.
        // Exactly one entry should carry this.
        'default' => true,

        // Addresses this server's callbacks arrive from. Hostnames are
        // resolved, plain IPs and CIDRs are matched directly. tusd is listed
        // alongside nginx because the tus webhook comes from its own container;
        // on a remote host both usually present the same address, and listing
        // both does no harm.
        'callback_sources' => array_filter(array_map('trim',
            explode(',', env('STREAMING_CALLBACK_SOURCES', 'streaming,tusd')))),

        // Reachable from the application, not from a browser: recording
        // control (nginx-rtmp's control module) and the RTMP pull the
        // thumbnail grabber uses.
        'host' => env('STREAMING_HOST', 'streaming'),
        'rtmp_port' => (int)env('STREAMING_RTMP_PORT', 1935),

        // Reachable from a browser: the origin /live and /videos-hls URLs are
        // built against. Defaults to the application's own origin, which is
        // what the transitional shims in nginx/nginx.*.conf serve.
        'public_host' => env('HLS_URL', env('APP_URL')),

        // Reachable from a broadcaster's encoder: RTMP ingest.
        'public_rtmp_url' => env('RTMP_URL'),
    ],

    // A second server needs no env vars -- it is a fixed deployment:
    //
    // 'eu-1' => [
    //     'callback_sources' => ['203.0.113.40'],
    //     'host' => 'stream-eu-1.internal',
    //     'public_host' => 'https://stream-eu-1.example.com',
    //     'public_rtmp_url' => 'rtmp://stream-eu-1.example.com:1935',
    // ],

];
