<?php
/*
  This file overrides settings in settings.php, and is gitignored, which means that you can use it to change configuration locally. This is a nifty place to put your password, for example.
*/

return [
    'settings' => [
        'db' => [
            'dsn'   => 'mysql:dbname=asset-library;host=127.0.0.1',
            'user'  => 'user',
            'pass'  => 'password',
        ],
        'auth' => [
            'secret' => 'secret',
        ],
    ],
];
