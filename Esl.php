<?php


class Esl
{
  private $login;
  private $password;

  public function __construct($login, $password)
  {
    $this->login = $login;
    $this->password = $password;
  }

  public static function fetch($url, $data = null)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');

    if($data) {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $content = curl_exec($ch);

    file_put_contents(__DIR__ . '/esl.html', $content);

    $dom = new DOMDocument("1.0", "UTF8");
    @$dom->loadHTML($content);

    return simplexml_import_dom($dom);
  }

  public function login()
  {

    file_put_contents(__DIR__ . '/cookie.txt', '');

    $url = 'https://secure3.eslpod.com/my-account/';
    $node = self::fetch($url);

    $nonce = (string) $node->xpath('//input[@id="woocommerce-login-nonce"]')[0]['value'];
    $ref   = (string) $node->xpath('//input[@name="_wp_http_referer"]')[0]['value'];

    $node = self::fetch($url, [
      'username' => $this->login,
      'password' => $this->password,
      'woocommerce-login-nonce' => $nonce,
      '_wp_http_referer' => $ref,
      'login' => 'Login'
    ]);

    $h4 = (string) $node->xpath('//h4')[0];

    return strpos($h4, 'Welcome') !== false;
  }
}

$config = require "config.php";
$esl = new Esl($config['login'], $config['password']);
if($esl->login()) {
  $node = Esl::fetch('https://secure3.eslpod.com/lesson-library');
  $as = $node->xpath('//a[@type="button"]');

  $pages = [];
  if($as) {
    foreach ($as as $a) {
      if(strpos($a, 'Daily English') !== false) {
        $pages[] = 'https://secure3.eslpod.com/library/' . str_replace('../', '', $a['href']) . '/';
      }
    }
  }

  $links = [];
  foreach ($pages as $page) {
    $node = Esl::fetch($page);
    $as = $node->xpath('//div[@class="col-sm-7"]/a');
    if($as) {
      foreach ($as as $a) {
        $links[] = [
          'name' => (string) $a,
          'href' =>  $a['href']
        ];
      }
    }
  }

  print_r($links);

} else {
  echo "Fail";
}