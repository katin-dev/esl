<?php
/* @var $app \Silex\Application */
$app = require "../app/app.php";

$app->get('/', function () use ($view, $esl) {
  $posts = $esl->getData()['posts'];
  //$postNames = array_map(function ($i) { return $i['name'];}, $posts);
  //array_multisort($postNames, SORT_DESC, $posts);
  $posts = array_reverse($posts);
  return $view->render("index", ['posts' => $posts]);
});

$app->run();