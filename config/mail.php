<?php

return [
    'mailer' => env('MAIL_MAILER', 'smtp'),
    'scheme' => env('MAIL_SCHEME'),
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => env('MAIL_PORT', 2525),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'url' => env('MAIL_URL'),
    'options' => [],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'MBG Central Kitchen'),
    ],
    'markdown' => [
        'theme' => 'default',
        'paths' => [resource_path('views/vendor/mail')],
    ],
];
