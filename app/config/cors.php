<?php

return [
    'paths' => [
        'api/*',
        'integrations/telegram/*',
    ],

    'allowed_methods' => [
        'GET',
        'POST',
        'OPTIONS',
    ],

    'allowed_origins' => [
        'http://admin.localhost:8055',
    ],

    'allowed_headers' => [
        '*',
    ],

    'supports_credentials' => false,
];
