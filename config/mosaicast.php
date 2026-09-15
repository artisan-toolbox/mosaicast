<?php

declare(strict_types=1);

return [

    'broadcasting' => [
        'guard' => 'mosaicast-session',
        'session_channel_key' => env('MOSAICAST_SESSION_CHANNEL_KEY', env('APP_KEY')),
        'session_channel_prefix' => 'mosaicast.sessions',
    ],

];
