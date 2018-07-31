<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $guarded = [];

    use SoftDeletes;
}
