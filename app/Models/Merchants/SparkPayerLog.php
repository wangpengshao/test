<?php

namespace App\Models\Merchants;

use Illuminate\Database\Eloquent\Model;

class SparkPayerLog extends Model
{
    protected $table = 'w_spark_payer_l';

    protected $fillable = [
        'pay_token', 'amount', 'type', 'openid', 'return_code', 'result_code', 'payment_no', 'partner_trade_no',
        'desc', 'response_info', 'current_money'
    ];
}
