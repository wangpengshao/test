<?php

namespace App\Models\Merchants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparkPayer extends Model
{
    use SoftDeletes;

    protected $table = 'w_spark_payer_c';

}
