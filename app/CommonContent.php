<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommonContent extends Model
{
    protected $table = 'common_contents';
    protected $primaryKey = 'id';
    protected $guarded = [];

    const RULE_TYPE = 1;
    const GUIDE_TYPE = 2;
    const DISPLAY_ZALO = 3;

}
