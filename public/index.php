<?php

require_once "../vendor/autoload.php";
require_once "../app/Esl.php";

define('__ROOT__', realpath(__DIR__ . '/..'));

$config = require __ROOT__ . "/app/configs/config.php";
$esl = new Esl($config['login'], $config['password'], __ROOT__ . '/data');

$data = $esl->getData();

/* @var $app Silex\Application */
$app   = new Silex\Application();
$view  = new League\Plates\Engine(__ROOT__ . '/app/views');
$app['debug'] = true;

$app->get('/', function () use ($view, $esl) {
  $posts = $esl->getData()['posts'];
  //$postNames = array_map(function ($i) { return $i['name'];}, $posts);
  //array_multisort($postNames, SORT_DESC, $posts);
  $posts = array_reverse($posts);
  return $view->render("index", ['posts' => $posts]);
});

$app->run();