<?php

namespace App\Console;
use App\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Grab extends Console
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

    $this->getLogger()->info('Try to log in...');
    if($esl->login()) {

      $this->getLogger()->info('Success login');
      $this->getLogger()->info("Try to grab posts...");
      $posts = $esl->grabPosts();
      if($posts) {

        $this->getLogger()->info(sprintf("Got %d posts", count($posts)));
        $newPostCount = 0;
        $stmtFind     = $this->getDb()->prepare("SELECT * FROM podcast WHERE name = :name");
        $stmtInsert   = $this->getDb()->prepare("INSERT INTO podcast(name) VALUES (:name)");
        foreach ($posts as $post) {
          // $id = preg_replace('/^.*?(\d+).*$/u', '\\1', $post['name']);
          $stmtFind->execute([':name' => $post['name']]);
          if ( !$stmtFind->fetch() ) {
            $stmtInsert->bindParam(':name', $post['name']);
            $stmtInsert->execute();
            $newPostCount++;
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

        $this->getLogger()->info($newPostCount ? sprintf("Found %s new podcasts", $newPostCount) : 'No new podcasts');
      } else {
        $this->getLogger()->error("Failed to grab posts");
      }
    } else {
      $this->getLogger()->error('Login failed');
    }
  }
}