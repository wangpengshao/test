<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class QrCodeLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'admin_wechat_qrcode_log';

    protected $fillable = ['rdid', 'token', 'openid', 't_id', 'isValid'];

    public function hasOneTask()
    {
        return $this->hasOne(QrTask::class, 'id', 't_id');
    }

    public function fans()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid');
    }

}
