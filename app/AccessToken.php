<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $table = 'access_tokens';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function prepareRefresh()
    {
        if($this->code_refresh == null) {
            $this->code_refresh = md5(uniqid());
            $this->save();
        }
        return;
    }

    public function refreshToken()
    {
        $this->expire_time = date('Y-m-d H:i:s',strtotime('+ 4hours'));
        $this->code_refresh = null;
        $this->save();
        return;
    }

    public function isExpire()
    {
        if($this->expire_time <= date('Y-m-d H:i:s')) {
            return true;
        }

        return false;
    }
}
