<?php

/**
 * Собирает картинку для превью ссылок (og:image) 1200×630: фирменный жёлтый фон и плитка «U» из иконки сайта
 * по центру. Только знак, без текста: VK и мессенджеры в чате показывают превью квадратной миниатюрой,
 * обрезая картинку по центру, и любой текст сбоку оказывался бы разрезан. Описание страницы соцсеть показывает рядом сама.
 * Вес около 20 КБ вместо 1,1 МБ у прежнего снимка с фотоаппарата.
 *
 * Использование:
 *   php tools/make-og-image.php
 */

const WIDTH = 1200;
const HEIGHT = 630;

$root = dirname(__DIR__);
$icon = $root . '/public/assets/front/images/favicon_io/android-chrome-512x512.png';
$out = $root . '/public/assets/front/images/og-default.jpg';

$image = imagecreatetruecolor(WIDTH, HEIGHT);
$yellow = imagecolorallocate($image, 0xf9, 0xca, 0x27);
imagefill($image, 0, 0, $yellow);

// Плитка по центру; в квадратной миниатюре (центральные 630×630) она остаётся целой с запасом по краям
$tile = 400;
$left = (int) ((WIDTH - $tile) / 2);
$top = (int) ((HEIGHT - $tile) / 2);
$source = imagecreatefrompng($icon);
imagecopyresampled($image, $source, $left, $top, 0, 0, $tile, $tile, imagesx($source), imagesy($source));

// Углы плитки скруглены цветом фона
$radius = 56;
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

imageinterlace($image, true);
imagejpeg($image, $out, 82);

printf("%s: %dx%d, %d КБ\n", $out, WIDTH, HEIGHT, (int) round(filesize($out) / 1024));
