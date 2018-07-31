<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany('App\Product');
    }

}
