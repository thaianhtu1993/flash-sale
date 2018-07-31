<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportReason extends Model
{
    protected $table = 'report_reasons';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
