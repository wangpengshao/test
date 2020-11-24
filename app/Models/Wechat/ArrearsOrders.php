<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class ArrearsOrders extends Model
{
    protected $table = 'financial_arrears_orders';

    protected $fillable = [
        'token', 'transaction_id', 'rdid', 'price', 'origin_price', 'cash_fee', 'openid', 'pay_status',
        'pay_type', 'prepay_id', 'order_id', 'pay_at'
    ];

    public function hasManyLog()
    {
        return $this->hasMany(ArrearsLog::class, 'order_id', 'order_id');
    }

    public function user()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid')->select(['openid', 'nickname']);
    }

    public function refunds()
    {
        return $this->hasMany(ArrearsRefund::class, 'order_id', 'order_id');
    }

    public function logList()
    {
        return $this->hasMany(ArrearsLog::class, 'order_id', 'order_id')
            ->select('order_id', 'feetype', 'status', 'tranid', 'price', 'id');
    }

}
