<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class CertificateRefund extends Model
{
    protected $table = 'admin_wechat_certificate_refund';

    protected $fillable = [
        'token', 'status', 'data', 'order_id', 'out_refund_no', 'refund_id', 'refund_fee', 'total_fee', 'refund_str'
    ];

    public function order()
    {
        return $this->belongsTo(CertificateLog::class, 'order_id', 'order_id');
    }


}
