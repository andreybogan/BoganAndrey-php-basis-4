<?php
/**
 * Функция проверяет имя загружаемого файла, если оно уже существует в дирректории,
 * то к имени файла добавляется индекс, если файл с индексом уже существует,
 * то он увеличивается на единицу.
 * @param string $file - Имя загружаемого файла.
 * @param string $dir - Путь к директории с файлами.
 * @return string Возвращается новое имя файла.
 */
function newFileName($file, $dir) {
  // Получаем массив файлов из дирректории и убираем из этого массива два первые элемента, а именно '.' и '..'
  $arrImages = array_slice(scandir($dir), 2);

  // Проверяем, есть ли в массиве файл с именем, совпадающим с именем загружаемого файла.
  if (in_array($file, $arrImages)) {
    // Разбираем файл на название и расширение, где $matches[1] - название, а $matches[2] - расширение.
    preg_match('/([^\s]+)\.((?:jp(?:e?g|e|2)|gif|png))$/i', $file, $matches);

    // проверяем, есть ли файл с индексом в массиве, если нет, прибавляем 1.
    $pattern = '/' . $matches[1] . '-(\d+)\.' . $matches[2] . '/i';

    // Обходим массив с файлами и проверяем есть ли совпадения для такого же файла но с индексом.
    $arrImgMatch = [];
    for ($i = 0; $i < count($arrImages); $i++) {
      // Если есть совпадение, то заносим его в массив.
      if (preg_match($pattern, $arrImages[$i], $matches2)) {
        $arrImgMatch[] = $matches[1] . "-" . ($matches2[1]) . "." . $matches[2];
      }
    }
    // Если файлы с индексом есть, то увеличиваем на единицу, иначе прибавляем индекс 1 и возвращаем.
    if (!empty($arrImgMatch)) {
      // Получаем из массива файл с максимальным индексом.
      $fileMaxIndex = $arrImgMatch[count($arrImgMatch) - 1];
      preg_match($pattern, $fileMaxIndex, $matches3);
      $file = $matches[1] . "-" . (++$matches3[1]) . "." . $matches[2];
    } else {
      $file = $matches[1] . "-1." . $matches[2];
    }
  }
  return $file;
}

/**
 * Функция изменяет размер картинки в зависимости от переданных параметров.
 * @param string $src - Путь к загружаемой картинке.
 * @param string $dest - Путь к новой картинке.
 * @param int $width - Максимальная ширина картинки.
 * @param int $height - Максимальная высота картинки.
 * @param string $change - Флаг преобразования: original, max, square.
 * @param int $quality - качество для jpg картинок. Принимате параметр от 0 до 100.
 * @return bool - При успешном создании изображении возвращается true, иначе false.
 */
function img_resize($src, $dest, $width, $height, $change, $quality = 100) {
  // Проверяем существование загружаемого файла.
  if (!file_exists($src)) {
    return false;
  }
  // Получение размеров файла и MIME типы.
  $size = getimagesize($src);
  // Получаем расширение файла.
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
  // Получаем значение ширины и высоты исходного изображения.
  list($img_width, $img_height) = $size;
  if (!$img_width || !$img_height) {
    return false;
  }

  // В зависимости от флага определяем новые параметры изображения,
  // такие как: новая ширина и высота, а так же координаты по x и y.

  // Оригинальное изображение.
  if ($change == 'original') {
    $new_width = $img_width;
    $new_height = $img_height;
    $x = 0;
    $y = 0;
  }

  // Если исходное изображение больше чем заданные в опции max значения,
  // то уменьшаем его до max значений, иначе оставляем прежним.
  if ($change == 'max') {
    $scale = min($width / $img_width, $height / $img_height);
    if ($scale >= 1) {
      $new_width = $img_width;
      $new_height = $img_height;
    } else {
      $new_width = $img_width * $scale;
      $new_height = $img_height * $scale;
    }
    $x = 0;
    $y = 0;
  }

  // Квадратное изображение.
  if ($change == 'square') {
    $min_wh = min($img_width, $img_height);

    $new_width = $width;
    $new_height = $height;

    if ($img_width > $img_height) {
      $x = ($img_width - $min_wh) / 2;
    } else {
      $x = 0;
    }
    $y = 0;
    $img_width = $min_wh;
    $img_height = $min_wh;
  }

  // создаем новое изображение в зависимости от формата.
  $new_img = @imagecreatetruecolor($new_width, $new_height);
  switch ($format) {
    case 'jpg':
    case 'jpeg':
      $src_img = @imagecreatefromjpeg($src);
      $write_image = 'imagejpeg';
      $image_quality = $quality;
      break;
    case 'gif':
      @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
      $src_img = @imagecreatefromgif($src);
      $write_image = 'imagegif';
      $image_quality = null;
      break;
    case 'png':
      @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
      @imagealphablending($new_img, false);
      @imagesavealpha($new_img, true);
      $src_img = @imagecreatefrompng($src);
      $write_image = 'imagepng';
      $image_quality = 9;
      break;
    default:
      $src_img = null;
  }
  // Копирование и изменение размера изображения записываем его в файлю
  $success = $src_img && @imagecopyresampled(
      $new_img,
      $src_img,
      0, 0, $x, $y,
      $new_width,
      $new_height,
      $img_width,
      $img_height
    ) && $write_image($new_img, $dest, $image_quality);

  @imagedestroy($src_img);
  @imagedestroy($new_img);
  return $success;
}