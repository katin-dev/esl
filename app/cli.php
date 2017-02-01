<?php
/* @var $app \Silex\Application */
$app = require "app.php";
new \App\Console\Grab();

$app = $app['console'];
$app->add(new \App\Console\Grab());
$app->add(new \App\Console\Download());
$app->run();