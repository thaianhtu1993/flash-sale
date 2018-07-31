<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsClick extends Model
{
    protected $table = 'sms_clicks';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
