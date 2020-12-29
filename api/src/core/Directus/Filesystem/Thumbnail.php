<?php

namespace Directus\Filesystem;

class Thumbnail
{
    /**
     * Thumbnail default format
     *
     * @var string
     */
    private static $defaultFormat = 'jpeg';

    private static $imageFormatsSupported = [
        'jpg',
        'jpeg',
        'gif',
        'png',
        'webp'
    ];

    /**
     * List of non-image supported extension/formats
     *
     * @var array
     */
    private static $nonImageFormatsSupported = [
        'svg',
        'pdf',
        'psd',
        'tif',
        'tiff',
    ];
    
    /**
     * List of video supported extension/formats
     *
     * @var array
     */
    private static $videoFormatsSupported = [
        'avi',
        'mp4',
        'mpg',
        'mpeg',
        'mov',
        'wmv',
        'flv',
        'webm',
        'mkv',
        'swf'
    ];

    public static function generateThumbnail($targetContent, $format, $thumbnailSize, $cropEnabled)
    {
        if (static::isNonImageFormatSupported($format)) {
            $format = static::defaultFormat();
            $targetContent = static::createImageFromNonImage($targetContent, $format);
        } else if (static::isVideoFormatSupported($format)) {
            $format = static::defaultFormat();
            $targetContent = static::createImageFromVideo($targetContent, $format, $thumbnailSize, $cropEnabled);
        }

        if (!in_array(strtolower($format), static::$imageFormatsSupported)) {
            return false;
        }

        if (!$targetContent) {
            return false;
        }

        $img = imagecreatefromstring($targetContent);

        if ($img === false) {
            return false;
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $x1 = 0; // used for crops
        $y1 = 0; // used for crops
        $aspectRatio = $w / $h;

        if ($cropEnabled) {
            // crop to center of image
            if ($aspectRatio <= 1) {
                $newW = $thumbnailSize;
                $newH = $h * ($thumbnailSize / $w);
                $y1 = -1 * (($newH - $thumbnailSize) / 2);
            } else {
                $newH = $thumbnailSize;
                $newW = $w * ($thumbnailSize / $h);
                $x1 = -1 * (($newW - $thumbnailSize) / 2);
            }
        } else {
            // portrait (or square) mode, maximize height
            if ($aspectRatio <= 1) {
                $newH = $thumbnailSize;
                $newW = $thumbnailSize * $aspectRatio;
            }
            // landscape mode, maximize width
            if ($aspectRatio > 1) {
                $newW = $thumbnailSize;
                $newH = $thumbnailSize / $aspectRatio;
            }
        }

        if ($cropEnabled) {
            $imgResized = imagecreatetruecolor($thumbnailSize, $thumbnailSize);
        } else {
            $imgResized = imagecreatetruecolor($newW, $newH);
        }

        // Preserve transperancy for gifs and pngs
        if ($format == 'gif' || $format == 'png' || $format == 'webp') {
            imagealphablending($imgResized, false);
            imagesavealpha($imgResized, true);
            $transparent = imagecolorallocatealpha($imgResized, 255, 255, 255, 127);
            imagefilledrectangle($imgResized, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($imgResized, $img, $x1, $y1, 0, 0, $newW, $newH, $w, $h);

        imagedestroy($img);
        return $imgResized;
    }

    /**
     * Create a image from a non image file content. (Ex. PDF, PSD or TIFF)
     *
     * @param $content
     * @param string $format
     *
     * @return bool|string
     */
    public static function createImageFromNonImage($content, $format = 'jpeg')
    {
        if (!extension_loaded('imagick')) {
            return false;
        }
        $image = new \Imagick();
        $image->readImageBlob($content);
        $image->setIteratorIndex(0);
        $image->setImageFormat($format);
        $image->setImageBackgroundColor('#ffffff');
        
        // Only Imagick 3.4.4 contains the ALPHACHANNEL_REMOVE constant
        if(\Imagick::IMAGICK_EXTNUM >= 30404){
            $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
        }
        else{
            $image->setImageAlphaChannel(12);
        }
        
        $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        
        return $image->getImageBlob();
    }
    

    /**
     * Create a image from a video file content. (Ex. AVI, MOV, or MP4)
     *
     * @param $content
     * @param string $format
     *
     * @return bool|string
     */
    public static function createImageFromVideo($content, $format, $thumbnailSize, $cropEnabled)
    {
        $tmpfname = tempnam(sys_get_temp_dir(), 'FOO') . ".png";

        $ffmpeg = FFMpeg\FFMpeg::create();
        $video = $ffmpeg->open($content);
        $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0));
        $frame->save($tmpfname);

        if (file_exists($tmpfname)) {
            $targetContent = base64_encode(imagecreatefrompng($tmpfname));
            return static::generateThumbnail($targetContent, 'jpeg', $thumbnailSize, $cropEnabled);
        } else {
            return false;
        }
    }

    public static function writeImage($extension, $path, $img, $quality)
    {
        ob_start();
        // force $path to be NULL to dump writeImage on the stream
        $path = NULL;
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($img, $path, $quality);
                break;
            case 'gif':
                imagegif($img, $path);
                break;
            case 'png':
                imagepng($img, $path);
                break;
	        case 'webp':
                imagewebp($img, $path, $quality);
                break;
            case 'pdf':
            case 'psd':
            case 'tif':
            case 'tiff':
                imagejpeg($img, $path, $quality);
                break;
        }
        return ob_get_clean();
    }

    /**
     * Gets the default thumbnail format
     *
     * @return string
     */
    public static function defaultFormat()
    {
        return static::$defaultFormat;
    }

    /**
     * Gets supported formats
     *
     * @return array
     */
    public static function getFormatsSupported()
    {
        return array_merge(static::getImageFormatSupported(), static::getNonImageFormatSupported());
    }

    /**
     * Gets image supported formats
     *
     * @return array
     */
    public static function getImageFormatSupported()
    {
        return Thumbnail::$imageFormatsSupported;
    }

    /**
     * Gets non-image supported formats
     *
     * @return array
     */
    public static function getNonImageFormatSupported()
    {
        return static::$nonImageFormatsSupported;
    }

    /**
     * If a given format/extension is a non-image supported to generate thumbnail
     *
     * @param $format
     *
     * @return bool
     */
    public static function isNonImageFormatSupported($format)
    {
        return in_array(strtolower($format), static::$nonImageFormatsSupported);
    }
    
    /**
     * If a given video is a non-image supported to generate thumbnail
     *
     * @param $format
     *
     * @return bool
     */
    public static function isVideoFormatSupported($format)
    {
        return in_array(strtolower($format), static::$videoFormatsSupported);
    }
}
