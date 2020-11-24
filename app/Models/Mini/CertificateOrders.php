<?php

namespace App\Models\Mini;

use Illuminate\Database\Eloquent\Model;

class CertificateOrders extends Model
{
    protected $table = 'admin_mini_certificate_orders';

    protected $fillable = [
        'token', 'mini_token', 'transaction_id', 'rdid', 'price', 'origin_price', 'cash_fee', 'openid', 'pay_status',
        'pay_type', 'prepay_id', 'order_id', 'pay_at'
    ];

    public function certificateLog()
    {
        return $this->belongsTo(CertificateLog::class, 'order_id', 'order_id');
    }


}
