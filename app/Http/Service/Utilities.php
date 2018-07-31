<?php
namespace App\Http\Service;

use App\ProductRate;
use App\UserRate;

class Utilities
{
    protected $imagePath;

    public function __construct()
    {
        $this->imagePath = public_path().'/image/';
    }

    public function uploadImage($image)
    {
        $imageName = md5(microtime()).'.'.$image->getClientOriginalExtension();
        $image->move($this->imagePath, $imageName);
        return $imageName;
    }

    public function replaceImage($image, $oldImage)
    {
        if(file_exists($this->imagePath.$oldImage)) {
            unlink($this->imagePath.$oldImage);
        }
        return $this->uploadImage($image);
    }

    public function userAccess()
    {
        return [
            'user',
            'admin'
        ];
    }

    public function productAccess()
    {
        return [
            'product',
        ];
    }



}


