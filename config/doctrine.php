<?php

declare(strict_types=1);

return [
    'connections' => [
        'master' => [
            'host' => 'postgres_primary',
            'port' => 5432,
            'dbname' => 'phpoo_app',
            'user' => 'postgres',
            'password' => 'postgres',
        ],
        'slave' => [
            'host' => 'haproxy',
            'port' => 5432,
            'dbname' => 'phpoo_app',
            'user' => 'postgres',
            'password' => 'postgres',
        ]
    ]
];
