<?php

require_once "Esl.php";
$config = require "configs/config.php";
$esl = new Esl($config['login'], $config['password'], __DIR__ .'/../data');
if($esl->login()) {

  echo "Logged in\n";

  $data = $esl->getData();
  $postNames = array_map(function ($post) { return $post['href']; }, $data['posts']);

  $posts = $esl->grabPosts();
  echo "Got " . count($posts) . " posts\n";

  $newPosts = [];
  foreach ($posts as $post) {
    if( !in_array($post['href'], $postNames) ) {
      $newPosts[] = $post;
    }
  }
  echo "Got " . count($newPosts) . " NEW posts\n";

  $esl->saveData(["posts" => $posts]);
  echo "Data saved\n";
}