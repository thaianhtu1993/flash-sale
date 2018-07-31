<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function user()
    {
        return $this->hasMany('App\User');
    }

    public function product()
    {
        return $this->hasMany('App\Product');
    }
}
