<?php

namespace App\Service;

class ImageService
{
    public static function resizeImagesInFolder($sourceFolder, $targetFolder, $targetSizeBytes)
    {
        if (!is_dir($sourceFolder) || !is_dir($targetFolder)) {
            return false;
        }

        $sourceFiles = glob($sourceFolder . '/*.*');

        foreach ($sourceFiles as $sourceFile) {
            $info = getimagesize($sourceFile);
            if (!$info) {
                continue; // Skip non-image files
            }

            $sourceMimeType = $info['mime'];

            switch ($sourceMimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($sourceFile);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($sourceFile);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($sourceFile);
                    break;
                default:
                    continue 2; // Unsupported image format
            }

            $imageData = self::resizeImage($image, $targetSizeBytes);

            $targetFile = $targetFolder . '/' . basename($sourceFile);
            switch ($sourceMimeType) {
                case 'image/jpeg':
                    imagejpeg($imageData, $targetFile);
                    break;
                case 'image/png':
                    imagepng($imageData, $targetFile);
                    break;
                case 'image/gif':
                    imagegif($imageData, $targetFile);
                    break;
            }

            imagedestroy($image);
            imagedestroy($imageData);
        }

        return true;
    }

    private static function resizeImage($image, $targetSizeBytes)
    {
        $quality = 90; // You can adjust the image quality here

        ob_start();
        imagejpeg($image, NULL, $quality);
        $imageData = ob_get_contents();
        ob_end_clean();

        if (strlen($imageData) <= $targetSizeBytes) {
            return $image;
        }

        $ratio = sqrt($targetSizeBytes / strlen($imageData));
        $width = imagesx($image);
        $height = imagesy($image);

        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $newImage;
    }
}