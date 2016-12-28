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
</head>
<body>
    <ul>
    <?php foreach($posts as $post): ?>
      <li><a href="<?=$post['href']?>"><?=$this->e($post['name'])?></a></li>
    <?php endforeach; ?>
    </ul>
</body>
</html>