<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class CertificateLog extends Model
{
    protected $table = 'admin_wechat_certificate_log';

    protected $fillable = [
        'token', 'status', 'type', 'rdname', 'rdpasswd', 'rdcertify', 'rdlib', 'operator', 'rdtype', 'data', 'is_pay',
        'prepay_id', 'order_id', 'openid', 'rdid', 'imgData', 'check_s', 'check_at', 'check_info'
    ];

    public function getDataAttribute($data)
    {
        if (is_array($data)) {
            return $data;
        }
        return (!empty($data)) ? json_decode($data, true) : [];
    }

    public function setDataAttribute($data)
    {
        if (is_array($data)) {
            $this->attributes['data'] = json_encode($data);
        }
    }

    public function orders()
    {
        return $this->hasOne(CertificateOrders::class, 'order_id', 'order_id');
    }

    public function user()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid')->select(['openid', 'nickname']);
    }

    public function refunds()
    {
        return $this->hasMany(CertificateRefund::class, 'order_id', 'order_id');
    }

    public function getImgDataAttribute($data)
    {
        if (is_array($data)) {
            return $data;
        }
        return (!empty($data)) ? json_decode($data, true) : [];
    }

    public function setImgDataAttribute($data)
    {
        if (is_array($data)) {
            $this->attributes['imgData'] = json_encode($data);
        }
    }


}
