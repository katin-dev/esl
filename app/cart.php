<?php

require_once "Esl.php";
$config = require "configs/config.php";
$esl = new Esl($config['login'], $config['password'], __DIR__ .'/../data');
if($esl->login()) {
  $esl->purchase([
    'https://secure3.eslpod.com/podcast/esl-podcast-1049-buying-theater-tickets/'
  ]);
}