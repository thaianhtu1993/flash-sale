<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $appends = ['status','code'];

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product','product_id');
    }

    public function rate()
    {
        return $this->hasOne('App\UserRate');
    }

    public function getIsAcceptAttribute($value)
    {
        return (bool) $value;
    }

    public function getIsSuccessAttribute($value)
    {
        return (bool) $value;
    }

    public function getStatusAttribute()
    {
        if(!$this->is_accept) {
            return 'not_accept';
        }
        elseif(!$this->is_success) {
            return 'accepted';
        }
        return 'complete';
    }

    public function getCodeAttribute()
    {
        $transactionService = \App::make('TransactionService');
        return $transactionService->createTransactionCode($this->id);
    }
}
