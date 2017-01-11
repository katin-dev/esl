<?php

require_once "Esl.php";
$config = require "configs/config.php";
$esl = new Esl($config['login'], $config['password'], __DIR__ .'/../data');
if($esl->login()) {

  $links = $esl->getAvailableLinks();

  foreach ($links as $link) {

    $name     = preg_replace('/[^-_+\w\9 ]/u', '', $link['name']);
    $filename = __DIR__ . '/../public/podcasts/' . $name . (strpos($name, 'MP3') ? '.mp3' : '.pdf');

    if( !file_exists($filename) ) {
      echo "Get $name...";
      if($content = $esl->fetch($link['link'])) {
        file_put_contents($filename, $content);
        echo "done\n";
      } else {
        echo "failed\n";
      }
    }
  }
}