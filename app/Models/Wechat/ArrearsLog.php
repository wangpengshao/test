<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class ArrearsLog extends Model
{
    protected $table = 'financial_arrears_log';

    protected $fillable = [
        'token', 'price', 'feetype', 'rdid', 'openid', 'status', 'is_pay', 'tranid', 'order_id', 'created_at'
    ];


    public function hasOneOrder()
    {
        return $this->hasOne(ArrearsOrders::class, 'order_id', 'order_id');
    }


    public function user()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid')->select(['openid', 'nickname']);
    }

    public function refunds()
    {
        return $this->hasMany(ArrearsRefund::class, 'order_id', 'order_id');
    }

}
