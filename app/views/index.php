<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ESL</title>
    <style>
        li {
            list-style: none;
        }
    </style>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <h1>ESL podcasts</h1>
        <ul class="list-unstyled">
          <?php foreach($posts as $post): ?>
              <li>
                  <a href="<?=$post['url']?>"><?=$this->e($post['name'])?></a>
                  <?php if(isset($post['files']['mp3'])): ?>
                      <a href="/podcasts/<?=$post['files']['mp3']['filename']?>"><span class="glyphicon glyphicon-volume-up"></span></a>
                  <?php endif; ?>
                  <?php if(isset($post['files']['pdf'])): ?>
                      <a href="/podcasts/<?=$post['files']['pdf']['filename']?>"><span class="glyphicon glyphicon-book"></span></a>
                  <?php endif; ?>
              </li>
          <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>