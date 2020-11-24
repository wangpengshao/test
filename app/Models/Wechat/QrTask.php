<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class QrTask extends Model
{
    protected $table = 'admin_wechat_qrcode_task';

    protected $casts = [
        'te1_da' => 'json',
        'te2_da' => 'json',
    ];

    public function belongsToQrConfig()
    {
       return $this->belongsTo(QrCodeConfig::class,'r_id','id');
    }

}
