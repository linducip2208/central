<?php

return [
    'defaults' => [
        'provider' => 'database',
    ],
    'providers' => [
        'database' => [
            'driver' => 'database',
            'table' => 'audit_logs',
            'morph_prefix' => 'auditable',
        ],
    ],
    'morph_map' => [],
];
