<?php

return [
    'debug' => env('APP_DEBUG', false),
    'environment' => env('APP_ENV', 'production'),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Shanghai',
    'components' => [
        'db' => include __DIR__ . '/db.php'
    ],
    'bootstrap' => ['db']
];