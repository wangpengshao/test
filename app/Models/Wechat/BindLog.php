<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class BindLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'admin_wechat_reader_log';

    protected $fillable = ['id', 'token', 'openid', 'rdid', 'created_at', 'type'];

    public function fansInfo()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid')->select('nickname', 'openid', 'headimgurl', 'token');
    }
}
