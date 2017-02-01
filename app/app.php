<?php

define('__ROOT__', realpath(__DIR__ . '/..'));

require_once __ROOT__ . '/vendor/autoload.php';
require_once __ROOT__ . '/app/Esl.php';

$config = require __DIR__ . "/configs/config.php";
/* @var $app Silex\Application */
$app   = new Silex\Application();
$app->register(new \Knp\Provider\ConsoleServiceProvider(), array(
  'console.name'              => 'Esl',
  'console.version'           => '1.0.0',
  'console.project_directory' => __ROOT__
));
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => __ROOT__ . '/data/app.log',
));
$app['esl'] = function () use ($config, $app) {
  return new \App\Esl($config['login'], $config['password'], __ROOT__ . '/data', $app['monolog']);
};
$app['view'] = function () use ($config) {
  return new \League\Plates\Engine(__ROOT__ . '/app/views');
};
$app['db'] = function () use ($config) {
  return new PDO('mysql:dbname='.$config['db']['dbname'].';host='.$config['db']['host'], $config['db']['username'], $config['db']['password']);
};
$app['conf'] = $config;
$app['debug'] = true;
return $app;