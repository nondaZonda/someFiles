<?php

namespace App\Service\Image;

use Cake\Core\Exception\Exception;
use Cake\Filesystem\File;

/**
 * Klasa tworzy obiekt graficzny, umożliwiający obróbkę przy pomocy klasy ImageKit
 * Współpracuje z plikami .jpg, .png
 * @package App\Service\Image
 */
class Image extends File
{
    protected $imageMimeTypeList = ['image/jpeg', 'image/png'];
    protected $imageResource = null;
    
    public $width = null;
    public $height = null;
    public $ratio = null;
    
    public function __construct($path)
    {
        parent::__construct($path, false);
        if (!$this->exists()) {
            throw new Exception('Plik nie istnieje !');
        }
        if (!in_array($this->mime(), $this->imageMimeTypeList)) {
            throw new Exception('Niedozwolony typ pliku !');
        }
        
        $this->setDimensions();
        $this->setImageResource($this->createImageResource());
    }
    
    protected function setDimensions()
    {
        $path = $this->Folder->path . DS . $this->name;
        list($this->width, $this->height) = getimagesize($path);
    }
    
    protected function createImageResource()
    {
        $imageResource = null;
        switch ($this->mime()) {
            case 'image/jpeg' :
                $imageResource = imagecreatefromjpeg($this->pwd());
                break;
            case 'image/png' :
                $imageResource = imagecreatefrompng($this->pwd());
                break;
        }
        
        return $imageResource;
    }
    
    public function setImageResource($imageResource)
    {
        $this->imageResource = $imageResource;
    }
    
    public function getImageResource()
    {
        return $this->imageResource;
    }
    
    public function save()
    {
        $path = $this->Folder->path . DS . $this->name;
        switch ($this->mime()) {
            case 'image/jpeg' :
                imagejpeg($this->imageResource, $path, 100);
                break;
            case 'image/png' :
                imagepng($this->imageResource, $path, 0);
                break;
        }
        $this->setDimensions();
    }
    
}