<?php

return [

    'enabled' => true,

    'endpoint' => '/metrics',

    'auth' => [
        'enabled' => env('PROMETHEUS_AUTH_ENABLED', false),
        'username' => env('PROMETHEUS_AUTH_USERNAME'),
        'password' => env('PROMETHEUS_AUTH_PASSWORD'),
    ],

    'storage' => [

        /**
         * The storage driver to use for Prometheus metrics.
         * <br>
         * Supported drivers: 'redis', 'database', 'pdo', 'memory'.
         */
        'driver' => env('PROMETHEUS_STORAGE_DRIVER', 'memory'),

        'redis' => [
            'connection' => env('PROMETHEUS_REDIS_CONNECTION', 'default'),
        ],

        'database' => [
            'connection' => env('PROMETHEUS_DB_CONNECTION', 'sqlite'),
            'prefix' => env('PROMETHEUS_DB_PREFIX', 'prometheus_'),
        ],

        'pdo' => [
            'dsn' => env('PROMETHEUS_PDO_DSN', 'sqlite:memory:'),
            'username' => env('PROMETHEUS_PDO_USERNAME'),
            'password' => env('PROMETHEUS_PDO_PASSWORD'),
            'options' => [
                // PDO Options
            ],
            'prefix' => env('PROMETHEUS_PDO_PREFIX', 'prometheus_'),
        ]
    ]

];

