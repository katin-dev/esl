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

        $findPodcastStmt = $this->getDb()->prepare("SELECT * FROM podcast WHERE slug = :slug");
        $updatePodcastStmt = $this->getDb()->prepare("UPDATE podcast SET purchased = 1, purchased_dt = NOW() WHERE id = :id AND purchased = 0");
        $findFileStmt = $this->getDb()->prepare("SELECT * FROM file WHERE podcast_id = :podcast_id AND filename = :filename");
        $insertFileStmt = $this->getDb()->prepare("INSERT INTO file(podcast_id, type, filename) VALUES (:podcast_id, :type, :filename)");

        foreach ($links as $link) {

          $findPodcastStmt->execute([
            'slug' => $this->getEsl()->shortName(trim($link['name']))
          ]);
          $podcast = $findPodcastStmt->fetch(\PDO::FETCH_ASSOC);

          if($podcast) {
            $name = trim($link['name']);
            $filetype = strpos($name, 'MP3') ? '.mp3' : '.pdf';
            $name = preg_replace('/–\s+(MP3|PDF)$/u', '', $name);
            $name = $this->getEsl()->normName($name);
            $filename = preg_replace('/\s/u', '_', $name) . $filetype;

            $findFileStmt->execute([
              'podcast_id' => $podcast['id'],
              'filename'   => $filename
            ]);
            $file = $findFileStmt->fetch(\PDO::FETCH_ASSOC);
            if( !$file ) {
              $dirname  = realpath($this->getSilexApplication()['conf']['podcasts_dir']);
              $this->getLogger()->info(sprintf("Try to download \"%s\"", $name));
              if($content = $this->getEsl()->fetch($link['link'])) {
                file_put_contents($dirname . DIRECTORY_SEPARATOR . $filename, $content);
                $insertFileStmt->execute([
                  'podcast_id' => $podcast['id'],
                  'type' => strpos($filename, '.mp3') ? 'mp3' : 'pdf',
                  'filename' => $filename
                ]);
                $updatePodcastStmt->execute([
                  'id' => $podcast['id']
                ]);
                $this->getLogger()->info("Success download");
              } else {
                $this->getLogger()->error(sprintf("Fail to download %s", $name));
              }
            }
          }
        }
      } else {
        $this->getLogger()->error("No links to download");
      }
    }
  }
}