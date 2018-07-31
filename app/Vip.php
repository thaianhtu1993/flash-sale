<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vip extends Model
{
    protected $table = 'vips';
    protected $primaryKey = 'id';
    protected $guarded = [];

    const NORMAL = 1;
    const VIP = 2;

    public function users()
    {
        return $this->hasMany('App\User');
    }
}
