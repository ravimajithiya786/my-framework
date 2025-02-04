<?php

return [
    'database' => [
        'mysql' => [
            'is_default' => true,
            'host' => $_ENV['DB_HOST'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'database' => $_ENV['DB_DATABASE'],
        ],
        'sqlite' => [
            'is_default' => false,
            'database' => $_ENV['DB_DATABASE'],
        ],

        'pgsql' => [
            'is_default' => false,
            'host' => $_ENV['DB_HOST'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'database' => $_ENV['DB_DATABASE'],
        ],
    ],  
    'cache' => [
        'redis' => [
            'is_default' => true,
            'host' => $_ENV['CACHE_HOST'],
            'port' => $_ENV['CACHE_PORT'],
        ],
    ],
    'mail' => [
        'smtp' => [
            'is_default' => true,
            'host' => $_ENV['MAIL_HOST'],
            'port' => $_ENV['MAIL_PORT'],
            'username' => $_ENV['MAIL_USERNAME'],
            'password' => $_ENV['MAIL_PASSWORD'],
        ],
    ],

    'session' => [
        'is_default' => true,
        'driver' => 'file',
        'lifetime' => 1440,
        'path' => '/',
    ]
];
