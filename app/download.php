<?php

require_once "Esl.php";
$config = require "configs/config.php";
$esl = new Esl($config['login'], $config['password'], __DIR__ .'/../data');
if($esl->login()) {

  $links = $esl->getAvailableLinks();

  foreach ($links as $link) {
    $content = $esl->fetch($link['link']);
    $name = trim(htmlspecialchars_decode($link['name']));
    file_put_contents(__DIR__ . '/../data/' .$name, $content);
  }
}