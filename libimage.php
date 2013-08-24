<?php
// Flora LibImage by LeaskH.com

class libImage {

    public function resizeImage($srcFile, $toWidth, $toHeight, $toFile = '', $toQuality = 100) {
        $tinfo = '';
        $data  = GetImageSize($srcFile, $tinfo);
        switch ($data[2]) {
            case 1:
                $curImage = ImageCreateFromGIF($srcFile);
                break;
            case 2:
                $curImage = ImageCreateFromJpeg($srcFile);
                break;
            case 3:
                $curImage = ImageCreateFromPNG($srcFile);
                break;
            default:
                return false;
        }

        $curWidth    = imagesx($curImage);
        $curHeight   = imagesy($curImage);

        $ratioWidth  = $toWidth  / $curWidth;
        $ratioHeight = $toHeight / $curHeight;

        $ratioX = $ratioWidth < $ratioHeight ? $ratioHeight : $ratioWidth;

        $draftWidth  = $curWidth  * $ratioX;
        $draftHeight = $curHeight * $ratioX;
        $draftImage  = imagecreatetruecolor($draftWidth, $draftHeight);
        imagealphablending($draftImage, false);
        imagesavealpha($draftImage, true);
        imagecopyresampled($draftImage, $curImage, 0, 0, 0, 0, $draftWidth,
                           $draftHeight, $curWidth, $curHeight);
        ImageDestroy($curImage);

        $newImage    = imagecreatetruecolor($toWidth, $toHeight);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        imagecopyresampled($newImage, $draftImage, 0, 0,
                           ($draftWidth  - $toWidth)/2,
                           ($draftHeight - $toHeight)/2,
                           $toWidth, $toHeight, $toWidth, $toHeight);

        if ($toFile === '') {
            return $newImage;
        }

        $toType = explode('.', $toFile);
        $toType = strtolower($toType[count($toType) - 1]);

        if (file_exists($toFile)) {
            unlink($toFile);
        }

        switch ($toType) {
            case 'gif':
                ImageGif($newImage, $toFile);
                break;
            case 'jpg':
            case 'jpeg':
                ImageJpeg($newImage, $toFile, $toQuality);
                break;
            case 'png':
                ImagePng($newImage, $toFile);
                break;
            default:
                return false;
        }

        ImageDestroy($draftImage);
        ImageDestroy($newImage);

        return true;
    }


    public function rawResizeImage($curImage, $toWidth, $toHeight) {
        $curWidth    = imagesx($curImage);
        $curHeight   = imagesy($curImage);

        $ratioWidth  = $toWidth  / $curWidth;
        $ratioHeight = $toHeight / $curHeight;

        $ratioX = $ratioWidth < $ratioHeight ? $ratioHeight : $ratioWidth;

        $draftWidth  = $curWidth  * $ratioX;
        $draftHeight = $curHeight * $ratioX;
        $draftImage  = imagecreatetruecolor($draftWidth, $draftHeight);
        // for alpha editing {
        imagealphablending($draftImage, true);
        imagesavealpha($draftImage, true);
        imagefill($draftImage, 0, 0, imagecolorallocatealpha($draftImage, 0, 0, 0, 127));
        // }
        imagecopyresampled($draftImage, $curImage, 0, 0, 0, 0, $draftWidth,
                           $draftHeight, $curWidth, $curHeight);
        ImageDestroy($curImage);

        $newImage    = imagecreatetruecolor($toWidth, $toHeight);
        // for alpha editing {
        imagealphablending($newImage, true);
        imagesavealpha($newImage, true);
        imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
        // }
        imagecopyresampled($newImage, $draftImage, 0, 0,
                           ($draftWidth  - $toWidth)  / 2,
                           ($draftHeight - $toHeight) / 2,
                           $toWidth, $toHeight, $toWidth, $toHeight);
        ImageDestroy($draftImage);

        return $newImage;
    }


    public function drawDrectangle($image, $left, $top, $width, $height, $rgbaColor) {
        imagefilledrectangle(
            $image, $left, $top, $left + $width, $top + $height,
            imagecolorallocatealpha(
                $image, $rgbaColor[0], $rgbaColor[1], $rgbaColor[2], $rgbaColor[3]
            )
        );
        return $image;
    }


    public function drawString($image, $left, $top, $string, $fontfile, $fontsize, $rgbColor, $bold = false) {
        $top += $fontsize;
        if ($bold) {
            $bold_x = array(1,  0,  1, 0, -1, -1, 1, 0, -1);
            $bold_y = array(0, -1, -1, 0,  0, -1, 1, 1,  1);
            for ($i = 0; $i <= 8; $i++) {
                imagettftext(
                    $image, $fontsize, 0, $left + $bold_x[$i], $top,
                    imagecolorallocate($image, $rgbColor[0], $rgbColor[1], $rgbColor[2]),
                    $fontfile, $string
                );
            }
        } else {
            imagettftext(
                $image, $fontsize, 0, $left, $top,
                imagecolorallocate($image, $rgbColor[0], $rgbColor[1], $rgbColor[2]),
                $fontfile, $string
            );
        }
        return $image;
    }


    public function imagetextouter(&$im, $size, $x, $y, $color, $fontfile, $text, $outer) {
        if (!function_exists('ImageColorAllocateHEX'))
        {
            function ImageColorAllocateHEX($im, $s)
            {
               if($s{0} == "#") $s = substr($s,1);
               $bg_dec = hexdec($s);
               return imagecolorallocate($im,
                           ($bg_dec & 0xFF0000) >> 16,
                           ($bg_dec & 0x00FF00) >>  8,
                           ($bg_dec & 0x0000FF)
                           );
            }
        }

        $ttf = false;

        if (is_file($fontfile))
        {
            $ttf = true;
            $area = imagettfbbox($size, $angle, $fontfile, $text);

            $width  = $area[2] - $area[0] + 2;
            $height = $area[1] - $area[5] + 2;
        }
        else
        {
            $width  = strlen($text) * 10;
            $height = 16;
        }

        $im_tmp = imagecreate($width, $height);
        $white = imagecolorallocate($im_tmp, 255, 255, 255);
        $black = imagecolorallocate($im_tmp, 0, 0, 0);

        $color = ImageColorAllocateHEX($im, $color);
        $outer = ImageColorAllocateHEX($im, $outer);

        if ($ttf)
        {
            imagettftext($im_tmp, $size, 0, 0, $height - 2, $black, $fontfile, $text);
            imagettftext($im, $size, 0, $x, $y, $color, $fontfile, $text);
            $y = $y - $height + 2;
        }
        else
        {
            imagestring($im_tmp, $size, 0, 0, $text, $black);
            imagestring($im, $size, $x, $y, $text, $color);
        }

        for ($i = 0; $i < $width; $i ++)
        {
            for ($j = 0; $j < $height; $j ++)
            {
                $c = ImageColorAt($im_tmp, $i, $j);
                if ($c !== $white)
                {
                    ImageColorAt ($im_tmp, $i, $j - 1) != $white || imagesetpixel($im, $x + $i, $y + $j - 1, $outer);
                    ImageColorAt ($im_tmp, $i, $j + 1) != $white || imagesetpixel($im, $x + $i, $y + $j + 1, $outer);
                    ImageColorAt ($im_tmp, $i - 1, $j) != $white || imagesetpixel($im, $x + $i - 1, $y + $j, $outer);
                    ImageColorAt ($im_tmp, $i + 1, $j) != $white || imagesetpixel($im, $x + $i + 1, $y + $j, $outer);
                    // 发光效果
                    /*
                    ImageColorAt ($im_tmp, $i - 1, $j - 1) != $white || imagesetpixel($im, $x + $i - 1, $y + $j - 1, $outer);
                    ImageColorAt ($im_tmp, $i + 1, $j - 1) != $white || imagesetpixel($im, $x + $i + 1, $y + $j - 1, $outer);
                    ImageColorAt ($im_tmp, $i - 1, $j + 1) != $white || imagesetpixel($im, $x + $i - 1, $y + $j + 1, $outer);
                    ImageColorAt ($im_tmp, $i + 1, $j + 1) != $white || imagesetpixel($im, $x + $i + 1, $y + $j + 1, $outer);
                    */
                }
            }
        }

        imagedestroy($im_tmp);
    }


    public function getImageCache($cachePath, $url, $period = 604800, $asImage = false, $format = 'png') { // 60 * 60 * 24 * 7
        if ($cachePath && $url && $period) {
            $hash = md5($url);
            $dir  = $cachePath
                  . '/' . substr($hash, 0, 1)
                  . '/' . substr($hash, 1, 2);
            $file = "{$dir}/{$hash}.{$format}";
            if (file_exists($file)
             && (time() - ($filemtime = filemtime($file)) <= $period)) {
                if ($asImage) {
                    switch ($format) {
                        case 'gif':
                            return @ImageCreateFromGIF($file);
                        case 'jpg':
                        case 'jpeg':
                            return @ImageCreateFromJpeg($file);
                        case 'png':
                        default:
                            return @ImageCreateFromPNG($file);
                    }
                } else if (($rsFile = @fopen($file, 'rb'))) {
                    return ['time' => $filemtime, 'resource' => $rsFile];
                }
            }
        }
        return null;
    }


    public function setImageCache($cachePath, $url, $image, $format = 'png', $quality = 100) {
        if ($cachePath && $url && $image) {
            $hash = md5($url);
            $dir  = $cachePath
                  . '/' . substr($hash, 0, 1)
                  . '/' . substr($hash, 1, 2);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    return false;
                }
            }
            $file = "{$dir}/{$hash}.{$format}";
            if (file_exists($file)) {
                unlink($file);
            }
            switch ($format) {
                case 'gif':
                    return @ImageGif($image, $file);
                case 'jpg':
                case 'jpeg':
                    return @ImageJpeg($image, $file, $quality);
                case 'png':
                default:
                    return @ImagePng($image, $file);
            }
        }
        return false;
    }


    // Inspired by: http://stackoverflow.com/questions/7203160/php-gd-use-one-image-to-mask-another-image-including-transparency
    public function imagealphamask(&$picture, $mask) {
        // Get sizes and set up new picture
        $xSize = imagesx($picture);
        $ySize = imagesy($picture);
        $newPicture = imagecreatetruecolor($xSize, $ySize);
        imagesavealpha($newPicture, true);
        imagefill($newPicture, 0, 0, imagecolorallocatealpha($newPicture, 0, 0, 0, 127));

        // Resize mask if necessary
        if ($xSize !== imagesx($mask) || $ySize !== imagesy($mask)) {
            $tempPic = imagecreatetruecolor($xSize, $ySize);
            imagecopyresampled($tempPic, $mask, 0, 0, 0, 0, $xSize, $ySize, imagesx($mask), imagesy($mask));
            imagedestroy($mask);
            $mask = $tempPic;
        }

        // Perform pixel-based alpha map application
        for ($x = 0; $x < $xSize; $x++) {
            for ($y = 0; $y < $ySize; $y++) {
                $alpha = imagecolorsforindex($mask, imagecolorat($mask, $x, $y));
                $color = imagecolorsforindex($picture, imagecolorat($picture, $x, $y));
                $alpha = 127 - floor((127 - $color['alpha']) * ($alpha['red'] / 255));
                if (127 == $alpha) { // int ? float
                    continue;
                }
                imagesetpixel($newPicture, $x, $y, imagecolorallocatealpha($newPicture, $color['red'], $color['green'], $color['blue'], $alpha));
            }
        }

        // Copy back to original picture
        imagedestroy($picture);
        $picture = $newPicture;
    }

}
