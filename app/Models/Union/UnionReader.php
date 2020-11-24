<?php

namespace App\Models\Union;

use Illuminate\Database\Eloquent\Model;

class UnionReader extends Model
{
    protected $table = 'w_union_reader';

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

    public function scopeCheckBind($query, $token, $openid)
    {
        return $query->where([
            'openid' => $openid,
            'token' => $token,
            'is_bind' => 1
        ]);
    }
}
