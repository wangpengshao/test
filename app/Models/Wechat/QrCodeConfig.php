<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class QrCodeConfig extends Model
{
    protected $table = 'admin_wechat_qrcode_config';

    public function hasOneTask()
    {
        return $this->hasOne(QrTask::class,'id','r_id');
    }

}
