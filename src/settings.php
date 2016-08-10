<?php
return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        // PDO configuration
        'db' => [
            'dsn'   => 'mysql:dbname=asset-library;host=127.0.0.1',
            'user'  => 'user', // Check settings-local.php for those two
            'pass'  => 'pass',
        ],

        // Auth configuration
        'auth' => [
            'secret' => 'somerandomstringshouldbeputhere', // Check settings-local.php
            'tokenExpirationTime' => 3600 * 24 * 7, // week
            'tokenSessionBytesLength' => 24, // If set to over 24 -- change DB schema
            'bcryptOptions' => [
              'cost' => 12,
            ],
        ],
    ],
];
