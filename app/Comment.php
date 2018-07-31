<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $hidden = ['product_id','user_id','rate_id','comment_parent_id'];
    protected $appends = array('anonymous_user_data');

    public function productRate()
    {
        return $this->belongsTo('App\ProductRate', 'rate_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function replies()
    {
        return $this->hasMany('App\Comment', 'comment_parent_id');
    }

    public function getAnonymousUserDataAttribute()
    {
        if ($this->anonymous_user != null) {
            return new User([
                'username' => 'anonymous',
                'avatar' => User::DEFAULT_AVATAR
            ]);
        }
    }

}
