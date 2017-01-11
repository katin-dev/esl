<?php

require_once "Esl.php";
$config = require "configs/config.php";
$esl = new Esl($config['login'], $config['password'], __DIR__ .'/../data');

if($esl->login()) {

  echo "Logged in\n";

  $data = $esl->getData();

  $posts = $esl->grabPosts();
  $postsByID = [];
  foreach ($posts as $k => $post) {
    $post['id'] = preg_replace('/^.*?(\d+).*$/u', '\\1', $post['name']);
    if(isset($data['posts'][$post['id']])) {
      $post = array_merge($data['posts'][$post['id']], $post);
    }
    $posts[$k] = $post;
    $postsByID[$post['id']] = $post;
  }

  $esl->saveData(["posts" => $postsByID]);

  if($coupon = $esl->getCoupon()) {
    $cnt = 0;
    for($i = count($posts) - 1; $i >= 0 && $cnt < min($coupon['remain'], 3); $i--) {
      if( empty($posts[$i]['purchased']) ) {
        $links[] = $posts[$i]['href'];
        $postsByID[$post['id']]['purchased'] = true;
        $cnt ++;
        echo $posts[$i]['name'] . "\n";
      }
    }
    $esl->purchase($links);
    $esl->saveData(["posts" => $postsByID]);
    echo "done\n";
  }

  echo "Data saved\n";
}