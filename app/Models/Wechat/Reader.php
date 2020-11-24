<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class Reader extends Model
{
    protected $table = 'admin_wechat_reader';

    protected $fillable = [
        'id',
        'token',
        'openid',
        'rdid',
        'password',
        'is_bind',
        'name',
        'origin_glc',//所在全局馆代码
        'origin_libcode',//所在分馆
        'is_cluster',//是否集群
    ];

    public function hasOneWechatinfo()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid');
    }

    public function scopeUserGetBind($query, $user)
    {
        return $query->where([
            'openid' => $user['openid'],
            'token' => $user['token'],
            'is_bind' => 1
        ]);
    }

    public function scopeCheckBind($query, $openid, $token)
    {
        return $query->where([
            'openid' => $openid,
            'token' => $token,
            'is_bind' => 1
        ]);
    }

    public function scopeRdidGetBind($query, $rdid, $token)
    {
        return $query->where([
            'rdid' => $rdid,
            'token' => $token,
            'is_bind' => 1
        ]);
    }

}
