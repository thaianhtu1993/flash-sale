<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    protected $table = 'titles';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany('App\User');
    }

}

