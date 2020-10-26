<?php

return [
    'namespaces' => [
        'Api' => base_path() . DIRECTORY_SEPARATOR . 'api',
        'Infrastructure' => base_path() . DIRECTORY_SEPARATOR . 'infrastructure'
    ],

    'protection_middleware' => [
        'auth:api'
    ],

    'extra_routes' => [
        'routes_v1' => [
            'middleware' => [],
            'namespace' => 'Controllers\V1',
            'prefix' => 'v1'
        ],
        'routes_v2' => [
            'middleware' => [],
            'namespace' => 'Controllers\V2',
            'prefix' => 'v2'
        ]
    ],

    'slack_formatter' => '\Infrastructure\Formatters\SlackFormatter'
];
