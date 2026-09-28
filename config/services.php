<?php

return [
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'log'),
        'api_url' => env('WHATSAPP_API_URL'),
        'api_key' => env('WHATSAPP_API_KEY'),
        'sender' => env('WHATSAPP_SENDER'),
    ],
    'ai' => [
        'provider' => env('AI_PROVIDER'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL'),
    ],
];
