<?php

return [
    'app_url' => env('APP_URL'),
    'app_domain'=> env('APP_DOMAIN'),
    'broadcast' => [
        'hls_url'=> env('HLS_URL', env('APP_URL')),
        'rtmp_url'=> env('RTMP_URL'),
        'rtmp_app_name'=> env('RTMP_APP_NAME', 'live'),
    ],
];
