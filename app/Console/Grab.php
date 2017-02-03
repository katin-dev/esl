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
      $esl->grabPosts(function ($posts) {
        $this->getLogger()->info(sprintf("Got %d posts", count($posts)));
        $newPostCount = 0;
        $stmtFind     = $this->getDb()->prepare("SELECT * FROM podcast WHERE slug = :slug");
        $stmtInsert   = $this->getDb()->prepare("INSERT INTO podcast(slug, name, url) VALUES (:slug, :name, :url)");
        foreach ($posts as $post) {
          $post['name'] = $this->getEsl()->normName($post['name']);
          $stmtFind->execute([':slug' => $post['id']]);
          if ( !$stmtFind->fetch() ) {
            $stmtInsert->bindParam(':slug',$post['id']);
            $stmtInsert->bindParam(':name',$post['name']);
            $stmtInsert->bindParam(':url', $post['href']);
            $stmtInsert->execute();
            $newPostCount++;
          }
        }

        $this->getLogger()->info($newPostCount ? sprintf("Found %s new podcasts", $newPostCount) : 'No new podcasts');
      });
    } else {
      $this->getLogger()->error('Login failed');
    }
  }
}