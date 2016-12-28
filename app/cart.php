<?php

require_once "Esl.php";
$config = require "configs/config.php";
$esl = new Esl($config['login'], $config['password'], __DIR__ .'/../data');
if($esl->login()) {

  $goods = [
    'https://secure3.eslpod.com/podcast/esl-podcast-1126-using-a-pawnshop/'
  ];

  foreach ($goods as $goodURL) {
    $esl->addToCart($goodURL);
  }

  $node = $esl->fetch('https://secure3.eslpod.com/cart/');
  $countNode = $node->xpath('//div[@class="discount-info"]/span[@class="number"]');
  if($countNode) {
    $remain = (int) $countNode[0];
  }

  $items = $node->xpath('//tr[@class="cart_item"]');

  if($items <= $remain) {
    // оформляем скидку и заказываем:
    $coupon = $node->xpath('//div[@class="coupon-container apply_coupons_credits blue medium"]')[0];
    $url = 'https://secure3.eslpod.com/?sc-page=cart&coupon-code=' . $coupon['name'];
    $esl->fetch($url);
    $esl->fetch('https://secure3.eslpod.com/checkout/');
  }
}