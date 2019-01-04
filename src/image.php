<?php
/**
 * creates empty image with color
 *
 * @param resource $image is the image resource
 * @param array $rgb the rgb values
 * @return resource the filled image
 */
function image_background($image, $rgb) {
    $rgb = array_values($rgb);

    imagefill($image, 0, 0, imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]));

    return $image;
}

/**
 * crop an image
 *
 * @param resource $image the image
 * @param int $x is the x axis of the point to crop from
 * @param int $y is the y axis of the point to crop from
 * @param int $width is the width of the crop
 * @param int $height is the hegiht of the crop
 * @return bool|resource the cropped image
 */
function image_crop($image, $x = 0, $y = 0, $width = null, $height = null) {
    list($imagex, $imagey) = image_wh($image);

    if (preg_match('/\%$/', $x)) {
        $x = $imagex * $x / 100;
    }

    if (preg_match('/\%$/', $y)) {
        $y = $imagey * $y / 100;
    }

    if (preg_match('/\%$/', $width)) {
        $width = $imagex * $width / 100;
    }

    if (preg_match('/\%$/', $height)) {
        $width = $imagey * $height / 100;
    }

    if (!$x && !$y && !$width && !$height || !$x && !$y && $width === $imagex && $height === $imagey) {
        return $image;
    }

    return imagecrop($image, [
        'x'      => $x,
        'y'      => $y,
        'width'  => $width ?: $imagex,
        'height' => $height ?: $imagey
    ]);
}

/**
 * dump an image to stdout or to a file
 *
 * @param resource $image is the image
 * @param string $type is the type - see image_ext()
 * @param string $file is the file to dump to - null to dump to stdout
 */
function image_dump($image, $type = 'jpeg', $file = null) {
    if (!function_exists($func = 'image' . $type)) {
        pfunc_error('Invalid image type ' . spy($type) . '.');
    }

    call_user_func($func, $image, $file);
}

/**
 * gets the image ext (i.e. gif, jpeg, png, etc)
 *
 * @param string $file is the image file path
 * @return string the image extention
 */
function image_ext($file) {
    return strtolower(str_remove('.', image_type_to_extension(exif_imagetype($file))));
}

/**
 * opens an image or creates a blank one
 *
 * @param string|int $file_or_w the path to the image file or the width
 * @param int $h the height of the new image
 * @return resource the image
 */
function image_open($file_or_w, $h = null) {
    $func = 'imagecreate';

    if (is_null($h)) {
        $func .= 'from' . image_ext($file_or_w);
    } else {
        $func .= 'truecolor';
    }

    if (!function_exists($func)) {
        pfunc_error('Function "' . $func . '" does not exist.');
    }

    return call_user_func_array($func, func_get_args());
}

/**
 * gets the image wiht and height
 *
 * @param resource $i is the image
 * @return array 0=>width, 1=>height
 */
function image_wh($i) {
    return [imagesx($i), imagesy($i)];
}

/**
 * resize an image
 *
 * @param resource $image the image
 * @param int $width new width
 * @param int $height new height
 * @return resource the new image
 */
function image_resize($image, $width = null, $height = null) {
    list($imagex, $imagey) = image_wh($image);

    if (!$width) {
        $width = $imagex;
    }

    if (!$height) {
        $height = $imagey;
    }

    if (preg_match('/\%$/', $width)) {
        $width = $imagex * $width / 100;
    }

    if (preg_match('/\%$/', $height)) {
        $width = $imagey * $height / 100;
    }

    if (!$width && !$height || $width === $imagex && $height === $imagey) {
        return $image;
    }

    $resized = imagecreatetruecolor($width, $height);

    imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $imagex, $imagey);

    return $resized;
}

/**
 * gets text width and height
 *
 * @param int $size the font size
 * @param int $angle the angle
 * @param string $font
 * @param string $text
 * @return array
 */
function image_textwh($size, $angle, $font, $text) {
    $boxxy = imagettfbbox($size, $angle, $font, $text);

    $xleft   = min([$boxxy[0], $boxxy[6]]);
    $xright  = max([$boxxy[2], $boxxy[4]]);
    $ytop    = min([$boxxy[5], $boxxy[7]]);
    $ybottom = max([$boxxy[1], $boxxy[3]]);

    return [
        'w' => $xright - $xleft,
        'h' => $ybottom - $ytop
    ];
}

/**
 * write an image to a file
 *
 * @param resource $image
 * @param string $file
 * @param string $type
 */
function image_write($image, $file, $type = 'jpeg') {
    image_dump($image, $type, $file);
}