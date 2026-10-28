<?php

/**
 * Собирает картинку для превью ссылок (og:image) 1200×630: фирменный жёлтый фон, плитка «U» из иконки сайта
 * и название. Только бренд, без подписей: описание страницы соцсеть показывает рядом с картинкой сама.
 * Вес около 40 КБ вместо 1,1 МБ у прежнего снимка с фотоаппарата.
 *
 * Использование (нужен шрифт Roboto Slab Bold в формате ttf; в репозитории его нет, берётся с Google Fonts):
 *   php tools/make-og-image.php /путь/к/RobotoSlab-Bold.ttf
 */

const WIDTH = 1200;
const HEIGHT = 630;

$font = $argv[1] ?? null;

if (! $font || ! is_file($font)) {
    fwrite(STDERR, "Укажите путь к шрифту RobotoSlab-Bold.ttf первым аргументом.\n");
    exit(1);
}

$root = dirname(__DIR__);
$icon = $root . '/public/assets/front/images/favicon_io/android-chrome-512x512.png';
$out = $root . '/public/assets/front/images/og-default.jpg';

$image = imagecreatetruecolor(WIDTH, HEIGHT);
$yellow = imagecolorallocate($image, 0xf9, 0xca, 0x27);
$ink = imagecolorallocate($image, 0x11, 0x11, 0x11);
imagefill($image, 0, 0, $yellow);

/** Размер шрифта, при котором текст помещается в ширину. */
$fit = function (string $text, int $size, int $maxWidth) use ($font): int {
    while ($size > 12) {
        $box = imagettfbbox($size, 0, $font, $text);

        if (($box[2] - $box[0]) <= $maxWidth) {
            break;
        }

        $size--;
    }

    return $size;
};

$name = 'Ufamasters';
$tile = 300;
$gap = 56;
$size = $fit($name, 108, 640);
$box = imagettfbbox($size, 0, $font, $name);
$textWidth = $box[2] - $box[0];

// Плитка и название стоят одной группой по центру
$groupWidth = $tile + $gap + $textWidth;
$left = (int) ((WIDTH - $groupWidth) / 2);
$top = (int) ((HEIGHT - $tile) / 2);

// Плитка с буквой U, углы скруглены цветом фона
$source = imagecreatefrompng($icon);
imagecopyresampled($image, $source, $left, $top, 0, 0, $tile, $tile, imagesx($source), imagesy($source));

$radius = 44;
foreach ([[0, 0], [1, 0], [0, 1], [1, 1]] as [$cx, $cy]) {
    for ($dx = 0; $dx < $radius; $dx++) {
        for ($dy = 0; $dy < $radius; $dy++) {
            $x = $cx ? $tile - 1 - $dx : $dx;
            $y = $cy ? $tile - 1 - $dy : $dy;
            $centerX = $cx ? $tile - $radius : $radius;
            $centerY = $cy ? $tile - $radius : $radius;

            if (hypot($x - $centerX + 0.5, $y - $centerY + 0.5) > $radius) {
                imagesetpixel($image, $left + $x, $top + $y, $yellow);
            }
        }
    }
}

// Название по вертикали по центру плитки: baseline считается из высоты букв
$baseline = (int) (HEIGHT / 2 - ($box[7] + $box[1]) / 2);
imagettftext($image, $size, 0, $left + $tile + $gap - $box[0], $baseline, $ink, $font, $name);

imageinterlace($image, true);
imagejpeg($image, $out, 82);

printf("%s: %dx%d, %d КБ\n", $out, WIDTH, HEIGHT, (int) round(filesize($out) / 1024));
