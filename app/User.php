<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const DEFAULT_AVATAR = 'user.png';

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['vip_id','role'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'vip_id','total_rate'
    ];

    public function getIsBannedAttribute($value)
    {
        return (bool) $value;
    }

    public function vip()
    {
        return $this->belongsTo('App\Vip','vip_id');
    }

    public function likes()
    {
        return $this->hasMany('App\Like');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function title()
    {
        return $this->belongsTo('App\Title', 'title_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function($user)
        {
            $user->password = bcrypt($user->password);
        });
    }

    public function getDefaultAvatar()
    {
        return 'user.png';
    }

    public function getCheckCount()
    {
        $this->check_count = 0;
        if($this->role != 'product') {
            $this->check_count = Product::with('productStatus', 'priority')
                ->whereHas(
                    'SmsClick',
                    function ($query) {
                        $query->where('user_id', $this->id);
                    }
                )->whereHas(
                    'CallClick',
                    function ($query) {
                        $query->where('user_id', $this->id);
                    }
                )->count();
        }

        return true;
    }
}
