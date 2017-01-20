<?php

namespace App;

class Esl
{
  private $login;
  private $password;
  private $dataPath;

  public function __construct($login, $password, $dataPath)
  {
    $this->login = $login;
    $this->password = $password;
    $this->dataPath = $dataPath;
  }

  public function getCookieFilename()
  {
    return $this->dataPath . DIRECTORY_SEPARATOR . 'cookie.txt';
  }

  public function getLogFilename()
  {
    return $this->dataPath . DIRECTORY_SEPARATOR . 'last-request.html';
  }

  public function getDataFilename()
  {
    return $this->dataPath . '/data.txt';
  }

  public function getData()
  {
    return file_exists($this->getDataFilename()) ? unserialize(file_get_contents($this->getDataFilename())) : ['posts' => []];
  }

  public function saveData($data)
  {
    file_put_contents($this->getDataFilename(), serialize($data));
  }

  public function fetch($url, $data = null, $headers = null)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->getCookieFilename());
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->getCookieFilename());
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');

    if($data) {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    if($headers) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $content = curl_exec($ch);

    file_put_contents($this->getLogFilename(), $content);

    return $content;
  }

  /**
   * @param string $content
   * @return SimpleXMLElement
   */
  public function createNode($content) {
    $dom = new DOMDocument("1.0", "UTF8");
    @$dom->loadHTML($content);
    return simplexml_import_dom($dom);
  }

  public function login()
  {

    file_put_contents($this->getCookieFilename(), '');

    $url = 'https://secure3.eslpod.com/my-account/';
    $node = $this->createNode($this->fetch($url));

    $nonce = (string) $node->xpath('//input[@id="woocommerce-login-nonce"]')[0]['value'];
    $ref   = (string) $node->xpath('//input[@name="_wp_http_referer"]')[0]['value'];

    $node = $this->createNode($this->fetch($url, [
      'username' => $this->login,
      'password' => $this->password,
      'woocommerce-login-nonce' => $nonce,
      '_wp_http_referer' => $ref,
      'login' => 'Login'
    ]));

    $h4 = (string) $node->xpath('//h4')[0];

    return strpos($h4, 'Welcome') !== false;
  }

  public function grabPosts()
  {
    $node = $this->createNode($this->fetch('https://secure3.eslpod.com/lesson-library'));
    $as = $node->xpath('//a[@type="button"]');

    $pages = [];
    if($as) {
      foreach ($as as $a) {
        if(strpos($a, 'Daily English') !== false) {
          $pages[] = 'https://secure3.eslpod.com/library/' . str_replace('../', '', $a['href']) . '/';
        }
      }
    }

    $posts = [];
    foreach ($pages as $page) {
      $node = $this->createNode($this->fetch($page));
      $as = $node->xpath('//div[@class="col-sm-7"]/a');
      if($as) {
        foreach ($as as $a) {
          $posts[] = [
            'name' => (string) $a,
            'href' => (string) $a['href']
          ];
        }
      }
    }

    return $posts;
  }

  public function getCoupon()
  {
    $url = 'https://secure3.eslpod.com/my-account/wc-smart-coupons/';
    $node = $this->createNode($this->fetch($url));
    $coupon = current($node->xpath('//div[@id="all_generated_coupon"]'));
    if($coupon) {
      return [
        'remain' => $coupon ? (int) current($coupon->xpath('//span[@class="number"]')) : 0,
        'id'     => $coupon ? (string) current($coupon->xpath('//div[@class="code"]')) : null
      ];
    }
  }

  /**
   * Положить в корзину
   * @param string $postURL
   */
  public function addToCart($postURL)
  {
    $node = $this->createNode($this->fetch($postURL));
    $a = $node->xpath('//a[@class="btn btn-default btn-buy"]');
    if($a) {
      $add2cartURL = 'https://secure3.eslpod.com' . $a[0]['href'];
      $this->fetch($add2cartURL);
    }
  }

  public function purchase($goods = [])
  {
    if( !$goods ) return true;

    $coupon = $this->getCoupon();

    if($coupon && $coupon['remain'] >= count($goods)) {
      // Добавляем в корзину:
      foreach ($goods as $goodURL) {
        $this->addToCart($goodURL);
      }

      // Оформляем скидку:
      $this->fetch('https://secure3.eslpod.com/?sc-page=cart&coupon-code=' . $coupon['id']);
      // Формляем заказ:
      $node = $this->createNode($this->fetch('https://secure3.eslpod.com/checkout/'));
      $nonce   = current($node->xpath('//input[@name="_wpnonce"]'));
      $referer = current($node->xpath('//input[@name="_wp_http_referer"]'));

      if($nonce && $referer) {
        $data = [
          'billing_first_name' => 'Sergey',
          'billing_last_name' => 'Katin',
          'billing_email' => 'gourry@mail.ru',
          'languages' => 'Русский язык',
          'billing_country' => 'RU',
          'billing_address_1' => 'Tunnelny proezd',
          'billing_address_2' => '',
          'billing_city' => 'Naro-Fominsk',
          'billing_state' => 'Moscow state',
          'billing_postcode' => '143302',
          '_wpnonce' => (string)$nonce['value'],
          '_wp_http_referer' => (string)$referer['value']
        ];

        $this->fetch('https://secure3.eslpod.com/checkout/?wc-ajax=checkout', $data, ['X-Requested-With: XMLHttpRequest']);

        return true;
      }
    }
  }

  public function getAvailableLinks()
  {
    $node = $this->createNode($this->fetch('https://secure3.eslpod.com/my-account/downloads/'));
    $table = current($node->xpath('//table[@class="woocommerce-MyAccount-downloads shop_table shop_table_responsive"]'));
    $links = [];
    if($table) {
      foreach ($table->xpath('//tr') as $key => $tr) {
        if($key > 0) {
          $links[] = [
            'name' => (string) $tr->td[0]->a,
            'link' => (string) $tr->td[3]->a['href']
          ];
        }
      }
    }

    return $links;
  }


}