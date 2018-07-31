<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $hidden = ['category_id','location_id','passcode','total_rate','product_status_id','priority_id'];

    public function comments()
    {
        return $this->hasMany('App\Comment', 'product_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Category','category_id');
    }

    public function location()
    {
        return $this->belongsTo('App\Location','location_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Tag','product_tag','product_id','tag_id');
    }

    public function likes()
    {
        return $this->hasMany('App\Like');
    }

    public function images()
    {
        return $this->hasMany('App\Image');
    }

    public function rates()
    {
        return $this->hasMany('App\ProductRate');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'product_id');
    }

    public function reports()
    {
        return $this->hasMany('App\ReportProduct');
    }

    public function productStatus()
    {
        return $this->belongsTo('App\ProductStatus', 'product_status_id');
    }

    public function priority()
    {
        return $this->belongsTo('App\Priority', 'priority_id');
    }

    public function smsClick()
    {
        return $this->hasMany('App\SmsClick', 'product_id');
    }

    public function callClick()
    {
        return $this->hasMany('App\CallClick', 'product_id');
    }

    public function changeAvatar($image)
    {
        $this->uploadAvatar($image, true);
    }

    public function uploadAvatar($avatar, $change = false)
    {

    }

}
