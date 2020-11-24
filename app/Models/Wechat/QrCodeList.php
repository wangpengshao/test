<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class QrCodeList extends Model
{
    protected $table = 'admin_wechat_qrcode_list';

    public $fillable = [
        'token', 'rdid', 't_id', 'url', 'ticket', 'status', 'type', 'expire_at'
    ];

    public function hasOneTask()
    {
        return $this->hasOne(QrTask::class,'id','t_id');
    }

}
