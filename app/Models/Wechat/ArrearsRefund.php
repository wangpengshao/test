<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class ArrearsRefund extends Model
{
    protected $table = 'financial_arrears_refund';

    protected $fillable = [
        'token', 'status', 'data', 'order_id', 'out_refund_no', 'refund_id', 'refund_fee', 'total_fee', 'refund_str'
    ];

    public function order()
    {
        return $this->belongsTo(ArrearsOrders::class, 'order_id', 'order_id');
    }


}
