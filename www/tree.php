<?php
// Поключаем файлы конфиг и функции.
include __DIR__ . "/../global/config.php";
include GLOBAL_DIR . "fns/fns_tree.php";
?>

<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Дерево каталога</title>
  <style>
    li {
      list-style-type: none;
      padding: 4px 0;
    }
    ul {
      margin-left: 12px;
      padding-left: 12px;
    }
  </style>
</head>
<body>
  <h1>Дерево каталога сайта: <?= $_SERVER['HTTP_HOST'] ?></h1>
  <p>Путь к каталогу: <?= DOCUMENT_ROOT ?></p>

  <?php
  // Печатаем каталог.
  printTree(DOCUMENT_ROOT );

  removeTree(WWW_DIR . "test");
  ?>
</body>
</html>
