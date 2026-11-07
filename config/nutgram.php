<?php
return [
    'token' => env('TELEGRAM_BOT_TOKEN'),
    // The existing webhook controller verifies our secret and records updates.
    'safe_mode' => false, 'routes' => true, 'mixins' => false,
    'namespace' => app_path('Telegram'), 'log_channel' => 'null',
    'config' => [
        'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
        'client' => array_filter(['proxy' => env('TELEGRAM_PROXY')]),
    ],
];
