<?php
/* @var $app \Silex\Application */
$app = require "../app/app.php";

$app->get('/', function () use ($app) {
  /* @var $db \PDO */
  $db = $app['db'];
  $stmt = $db->query("SELECT * FROM podcast ORDER BY id DESC");
  $podcasts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

  $stmt = $db->query("SELECT podcast_id, type, filename FROM file");
  $files = $stmt->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);

  foreach ($podcasts as $key => $podcast) {
    if(isset($files[$podcast['id']])) {
      foreach ($files[$podcast['id']] as $file) {
        if($file['type'] == 'pdf') {
          $podcasts[$key]['files']['pdf'] = $file;
        }
        if($file['type'] == 'mp3') {
          $podcasts[$key]['files']['mp3'] = $file;
        }
      }
    }
  }

  return $app['view']->render("index", ['posts' => $podcasts]);
});

$app->run();