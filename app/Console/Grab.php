<?php

namespace App\Console;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Grab extends Command
{
  protected function configure()
  {
    $this
      ->setName("app:grab")
      ->setDescription("Grabs new podcasts from site");
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    /* @var $esl \App\Esl */
    $app    = $this->getSilexApplication();
    $esl    = $app['esl'];

    if($esl->login()) {

      $app['monolog']->info("Logged in successefull");

      $posts = $esl->grabPosts();
      $app['monolog']->info(sprintf("Got %d posts", count($posts));
      $posts = array_map(function ($post) { $post['id'] = preg_replace('/^.*?(\d+).*$/u', '\\1', $post['name']); return $post; }, $posts);

      // Save posts into DB
      /* @var $stmt \PDOStatement */
      $stmtFind   = $app['db']->prepare("SELECT * FROM podcast WHERE name = :name");
      $stmtInsert = $app['db']->prepare("INSERT INTO podcast(name) VALUES (:name)");
      $newPostCount = 0;
      foreach ($posts as $post) {
        $stmtFind->execute([
          ':name' => $post['name']
        ]);
        if( !$stmt->fetch() ) {
          $stmtInsert->bindParam(':name', $post['name']);
          $stmtInsert->execute();
          $newPostCount ++;
        }
      }

      /*if($coupon = $esl->getCoupon()) {
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
      }*/

      $app['monolog']->error(sprintf("Found %s new podcasts", $newPostCount));
    } else {
      $app['monolog']->error("Can't log in");
    }
  }

}