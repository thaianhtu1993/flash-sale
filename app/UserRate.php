<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRate extends Model
{
    protected $table = 'user_rates';
    protected $primaryKey = 'id';
    protected $hidden = ['product_id','user_id','transaction_id'];
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product','product_id');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Transaction', 'transaction_id');
    }
}
