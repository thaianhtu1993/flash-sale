<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportProduct extends Model
{
    protected $table = 'report_products';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }
}
