<?php

namespace App\Models\Merchants;

use Illuminate\Database\Eloquent\Model;

class SparkPayerUpLog extends Model
{
    protected $table = 'w_spark_payer_up_log';
    const UPDATED_AT = null;
    protected $fillable = [
        'c_id', 'current_money', 'desc', 'number', 'created_at'
    ];
}
