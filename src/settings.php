<?php
return [
    'settings' => [
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
            'dsn'   => 'mysql:dbname=addonlib;host=127.0.0.1',
            'user'  => 'user', // Check settings-local.php for those two
            'pass'  => 'pass',
        ],
    ],
];
