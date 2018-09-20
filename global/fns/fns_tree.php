<?php
/**
 * Функция выполняет рекурсивный обход и выводит имена подкатологов и файлов в текущем каталоге.
 * @param string $dir Путь к каталогу.
 */
function printTree($dir) {
  // Открываем каталог и выходим в случае ошибки.
  $dr = @opendir($dir);
  if (!$dr) {
    return;
  }
  echo "<ul>";
  // Обходим в цикле каталог и получаем его элементы.
  while (($elem = readdir($dr)) !== false) {
    // Игнорируем элементы (.) и (..), а так же каталоги .git и .idea
    if ($elem == '.' || $elem == '..' || $elem == '.git' || $elem == '.idea') {
      continue;
    }
    // Если элемент является каталогом выделяем его и вызываем нашу же функцию.
    if (is_dir($dir . "/" . $elem)) {
      echo "<li><span style='font-weight: bold; text-decoration: underline;'> " . $elem . "</span>";
      printTree($dir . "/" . $elem);
      echo "</li>";
      continue;
    }
    // Если элемент не является каталогом, просто выводим его.
    echo "<li>" . $elem . "</li>";
  }
  echo "</ul>";
  closedir($dr);
}