<?php

namespace App\Service;

use App\Entity\Photo;

class DataPhotoService
{
    public function setDataPhoto($url): Photo
    {
        $fileInfo = new \SplFileInfo($url);
        $photo = new Photo();

        list($imgWidth, $imgHeight, $imgType) = getimagesize($url);
        $uniqueFilename = uniqid('', true);

        $photo->setWidth($imgWidth);
        $photo->setHeight($imgHeight);
        $photo->setName($fileInfo->getFilename());
        $photo->setPath($uniqueFilename . image_type_to_extension($imgType));

        return $photo;
    }
}
