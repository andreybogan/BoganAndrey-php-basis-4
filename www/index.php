<?php
// Поключаем файлы конфиг и функции.
include __DIR__ . "/../global/config.php";
include GLOBAL_DIR . "fns/fns_upload.php";

// Каталог для хранения превьюшек фотографий.
$dirImgSmall = "images/small/";
// Каталог для хранения оригиналов фотографий.
$dirImgBig = "images/big/";
// Загружаемый файл.
$file = 'file';
// определяем допустимые расширения файлов
$accept_file_types = '/[^\s]+\.(?:jp(?:e?g|e|2)|gif|png|pdf)$/i';
// Инициируем переменную для вывода ошибок.
$error = '';

// Проверяем была ли нажата кнопка Добавить фото.
if (@$_REQUEST['uploadPhoto']) {
// Если при загрузке файла переменная error содержит ошибки выводим сообщение об этом.
  if ($_FILES[$file]['error'] > 0) {
    echo "При загрузке файла возникла проблема: ";
    switch ($_FILES[$file]['error']) {
      case 1:
        $error = "Размер файла превышает значение upload_max_filesize.";
        break;
      case 2:
        $error = "Размер файла превышает значение max_file_size.";
        break;
      case 3:
        $error = "Файл загружен только частично.";
        break;
      case 4:
        $error = "Файл не был загружен.";
        break;
      case 6:
        $error = "Не удалось загрузить файл: не указан временный каталог.";
        break;
      case 7:
        $error = "Загрузка потерпела неудачу: не удалось выполнить запись на диск.";
        break;
      case 8:
        $error = "Расширение PHP заблокировало загрузку файла.";
        break;
    }
  } else {
    // Проверяем действительно ли файл был загружен, и не является ли он локальным файлом.
    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
      // Проверяем имеет ли файл допустимое расширение.
      if (preg_match($accept_file_types, $_FILES['file']['name'])) {
        // Проверяем имя файла, если оно повторяется, то изменяем его.
        $newFileName = newFileName($_FILES['file']['name'], WWW_DIR . $dirImgSmall);
        img_resize($_FILES[$file]['tmp_name'], WWW_DIR . $dirImgBig . $newFileName, 1080,
                   720, "max", 80);
        img_resize($_FILES[$file]['tmp_name'], WWW_DIR . $dirImgSmall . $newFileName, 200,
                   200, "square", 80);
        // Редирект на саму себя.
        header("Location:  http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);
      } else {
        $error = "Файл не является изображением.";
      }
    } else {
      $error = "Возможная атака через загрузку файла: {$_FILES[$file]['name']}";
      exit;
    }
  }
}

// Получаем массив картинок из дирректории и убираем из него два первые элемента, а именно '.' и '..'
$arrSmallImages = array_slice(scandir(WWW_DIR . $dirImgSmall), 2);
?>

<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Фотоальбом</title>
</head>
<body>
  <?php
  // Если в дирректории есть фотографии, то выводим их на страницу.
  if (!empty($arrSmallImages)) {
    for ($i = 0; $i < count($arrSmallImages); $i++) {
      ?> <a href="<?= $dirImgBig . $arrSmallImages[$i] ?>" target="_blank"><img
            src="<?= $dirImgSmall . $arrSmallImages[$i] ?>" alt="Фото в альбоме"></a> <?
    }
  }

  // Если при загрузке файла возникли ошибки, выводим их.
  if (!empty($error)) {
    echo "<p style='color:red;'>{$error}</p>";
  }
  ?>

  <!-- Форма добавления нового фото -->
  <form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST" enctype="multipart/form-data" style="margin: 24px 0;">
    <input type="file" name="file">
    <input type="submit" name="uploadPhoto" value="Добавить фото">
  </form>
</body>
</html>