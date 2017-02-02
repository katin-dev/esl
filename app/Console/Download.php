<?php

namespace App\Console;
use App\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends Console
{
  protected function configure()
  {
    $this
      ->setName("app:download")
      ->setDescription("Downloads available podcasts");
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->getLogger()->info("Try to log in");
    if( $this->getEsl()->login() ) {
      $this->getLogger()->info("Success logiin");
      $this->getLogger()->info("Try to get available podcasts");
      if($links = $this->getEsl()->getAvailableLinks()) {
        $this->getLogger()->info(sprintf("Got %d available links to download", count($links)));

        foreach ($links as $link) {
          $name     = preg_replace('/[^-_+\w\9 ]/u', '', $link['name']);
          $dirname  = $this->getSilexApplication()['conf']['podcasts_dir'];
          $filename = $dirname .  '/' . $name . (strpos($name, 'MP3') ? '.mp3' : '.pdf');
          if( !file_exists($filename) ) {
            $this->getLogger()->info(sprintf("Try to download \"%s\"", $name));
            if($content = $this->getEsl()->fetch($link['link'])) {
              file_put_contents($filename, $content);
              $this->getLogger()->info("Success download");
            } else {
              $this->getLogger()->error(sprintf("Fail to download %s", $name));
            }
          }
        }
      } else {
        $this->getLogger()->error("No links to download");
      }
    }
  }
}