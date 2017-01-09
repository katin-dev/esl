<?php

require_once "Esl.php";
$config = require "configs/config.php";
$esl = new Esl($config['login'], $config['password'], __DIR__ .'/../data');
if($esl->login()) {

  $links = $esl->getAvailableLinks();

  foreach ($links as $link) {
    if($content = $esl->fetch($link['link'])) {
      $name = trim($link['name']);
      $name = preg_replace('/[^-_+\w\9 ]/u', '', $name);
      if( strpos($name, 'MP3') !== false ) {
        $filename = $name . '.mp3';
      } else {
        $filename = $name . '.pdf';
      }
      file_put_contents(__DIR__ . '/../data/' . $filename, $content);
      echo $filename . "\n";
    }
  }
}