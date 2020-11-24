<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class QrCodeSeo extends Model
{
    protected $table = 'admin_wechat_qrcode_seo';

    public $fillable = [
        'token', 'invites', 'views', 'keyword', 'url', 'ticket', 'status', 'group_id', 'type', 'expire_at', 'title'
    ];

}
