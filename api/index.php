<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

if (!defined('FRONTEND')) {
    define('FRONTEND', false);
}

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$local_settings = require __DIR__ . '/../src/settings-local.php';

$c = new \Slim\Container(array_replace_recursive($settings, $local_settings));
$c['foundHandler'] = function ($c) {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};
$app = new \Slim\App($c);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
