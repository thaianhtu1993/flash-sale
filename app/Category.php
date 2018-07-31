<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function product()
    {
        return $this->hasMany('App\Product')->with('productStatus', 'priority')->take(6);
    }
}
