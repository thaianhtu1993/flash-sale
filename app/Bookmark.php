<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $table = 'bookmarks';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $hidden = ['user_id','product_id','id','updated_at','created_at'];

    public function product()
    {
        return $this->belongsTo('App\Product','product_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

}
