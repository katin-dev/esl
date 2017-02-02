<?php
/* @var $app \Silex\Application */
$app = require "../app/app.php";

$app->get('/', function () use ($app) {
  /* @var $db \PDO */
  $db = $app['db'];
  $stmt = $db->query("SELECT * FROM podcast ORDER BY id DESC");
  $podcasts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
  var_dump($podcasts);
  return $app['view']->render("index", ['posts' => $podcasts]);
});

$app->run();