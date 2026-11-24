<?php

return [

    'apps' => [
        [
            'id' => env('REVERB_APP_ID', 'sicofil-app'),
            'name' => env('APP_NAME', 'SICOFIL'),
            'key' => env('REVERB_APP_KEY', 'sicofil-key'),
            'secret' => env('REVERB_APP_SECRET', 'sicofil-secret'),
            'path' => env('REVERB_APP_PATH', ''),
            'capacity' => null,
            'enable_client_messages' => true,
            'enable_usage_metrics' => false,
        ],
    ],

    'apps_provider' => \Laravel\Reverb\ApplicationManager::class,

    'connection' => env('REVERB_CONNECTION', 'reverb'),

    'dashboard' => [
        'domain' => env('REVERB_DASHBOARD_DOMAIN'),
        'path' => env('REVERB_DASHBOARD_PATH', 'reverb'),
        'port' => env('REVERB_DASHBOARD_PORT', 8082),
        'middleware' => [
            'web',
            \Laravel\Reverb\Http\Middleware\AuthenticateDashboard::class,
        ],
    ],

];
