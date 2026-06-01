<?php

return [
    'ignored_methods' => [
        'GET',
        'HEAD',
        'OPTIONS',
    ],

    'actions' => [
        'POST' => 'CREATED',
        'PUT' => 'UPDATED',
        'PATCH' => 'UPDATED',
        'DELETE' => 'DELETED',
    ],

    'objects' => [
        'users' => 'USER',
        'texts' => 'TEXT',
    ],

    'item_id_header' => 'X-ITEM-ID',

    'route_id_parameters' => [
        'id',
    ],

    'middleware_group' => 'web',

    'route_middlewares' => [],

    'domain' => null,
];
