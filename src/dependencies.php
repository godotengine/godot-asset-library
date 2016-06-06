<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// pdo
$container['db'] = function ($c) {
  $settings = $c->get('settings')['db'];
  $db = new PDO($settings['dsn'], $settings['user'], $settings['pass']);
  return $db;
};

// constants
$container['constants'] = function ($c) {
  return require_once __DIR__ . '/constants.php';
};

// queries
$container['queries'] = function ($c) {
  $db = $c->db;
  $constants = $c->constants;

  $raw_queries = require_once __DIR__ . '/queries.php';
  $queries = [];
  foreach ($raw_queries as $model => $model_queries) {
    $queries[$model] = [];
    foreach ($model_queries as $query_name => $query) {
      $queries[$model][$query_name] = $db->prepare($query);
      $queries[$model][$query_name]->setFetchMode(PDO::FETCH_ASSOC);
    }
  }
  return $queries;
};

// tokens
$container['tokens'] = function ($c) {
  require_once __DIR__ . '/helpers/tokens.php';
  return new Tokens($c);
};

// utils
$container['utils'] = function ($c) {
  require_once __DIR__ . '/helpers/utils.php';
  return new Utils($c);
};
