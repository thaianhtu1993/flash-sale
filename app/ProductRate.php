<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRate extends Model
{
    protected $table = 'product_rates';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $hidden = ['product_id','user_id'];

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product','product_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment','rate_id');
    }

}
