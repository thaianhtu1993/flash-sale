<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    const HOT = 1;
    const VIP = 2;
    const BRAND_NEW = 3;

    protected $table = 'priorities';
    protected $primaryKey = 'id';
    protected $hidden = ['created_at','updated_at'];
}
