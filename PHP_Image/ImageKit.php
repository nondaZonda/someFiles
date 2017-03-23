<?php

namespace App\Service\Image;

class ImageKit
{
    /**
     * Zmiana rozmiaru obrazka z zachowaniem stałej szerokości
     * @param \App\Service\Image\Image $image
     * @param $width
     * @return \App\Service\Image\Image
     */
    public function resizeProportionalByWidth(Image $image, $width)
    {
        return $this->resize($image, $width, null);
    }
    
    /**
     * Zmiana rozmiaru obrazka z zachowaniem stałej wysokości
     * @param \App\Service\Image\Image $image
     * @param $height
     * @return \App\Service\Image\Image
     */
    public function resizeProportionalByHeight(Image $image, $height)
    {
        return $this->resize($image, null, $height);
    }
    
    /**
     * @param \App\Service\Image\Image $image
     * @param $width
     * @param $height
     * @return \App\Service\Image\Image | null
     */
    public function resize(Image $image, $width = null, $height = null)
    {
        $resultWidth = $width;
        $resultHeight = $height;
        $originalImageRatio = $image->width / $image->height;
        
        if (is_null($height)) {
            $resultWidth = $width;
            $resultHeight = $width / $originalImageRatio;
        }
        if (is_null($width)) {
            $resultWidth = $height * $originalImageRatio;
            $resultHeight = $height;
        }

        $resizedImage = imagecreatetruecolor($resultWidth, $resultHeight);
        imagecopyresampled($resizedImage, $image->getImageResource(), 0, 0, 0, 0, $resultWidth, $resultHeight, $image->width, $image->height);
    
        $image->setImageResource($resizedImage);
    
        return $image;
    }
    
    /**
     * Zmiana rozmiaru obrazka z przycięciem do zadanego wymiaru
     * @param \App\Service\Image\Image $image
     * @param $width
     * @param $height
     * @return \App\Service\Image\Image | null
     */
    public function resizeWithCrop(Image $image, $width, $height)
    {
        $originalImageRatio = $image->width / $image->height;
        $requiredImageRatio = $width / $height;
        
        $diffRatios = $originalImageRatio - $requiredImageRatio;
        switch ($diffRatios) {
            case $diffRatios > 0:
                $image = $this->resizeProportionalByHeight($image, $height);
                $image = $this->crop($image, $width, $height);
                break;
            case $diffRatios < 0:
                $image = $this->resizeProportionalByWidth($image, $width);
                $image = $this->crop($image, $width, $height);
                break;
            default:
                return null;
                break;
        }
        
        return $image;
    }
    
    /**
     * Przycięcie obrazka do zadanego kwadratu
     * @param \App\Service\Image\Image $image
     * @param $width
     * @param $height
     * @return \App\Service\Image\Image
     */
    public function crop(Image $image, $width, $height)
    {
        $rectangle = [
            'x' => 0,
            'y' => 0,
            'width' => $width,
            'height' => $height
        ];
        $resultImage = imagecrop($image->getImageResource(), $rectangle);
        $image->setImageResource($resultImage);
        
        return $image;
    }
    
    /**
     * @param \App\Service\Image\Image $image
     * @param $degrees
     * @return \App\Service\Image\Image
     */
    public function rotate(Image $image, $degrees)
    {
        $resultImage = imagerotate($image->getImageResource(), $degrees, 0);
        $image->setImageResource($resultImage);
        
        return $image;
    }
    
}