<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'pic' => [
            'driver' => 'session',
            'provider' => 'pics',
        ],
        'marketing' => [
            'driver' => 'session',
            'provider' => 'marketings',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'pics' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pic::class,
        ],
        'marketings' => [
            'driver' => 'eloquent',
            'model' => App\Models\Marketing::class,
        ],
    ],
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
    'password_timeout' => 10800,
];
