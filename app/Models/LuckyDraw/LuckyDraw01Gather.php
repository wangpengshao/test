<?php

namespace App\Models\LuckyDraw;

use Illuminate\Database\Eloquent\Model;

class LuckyDraw01Gather extends Model
{
    const UPDATED_AT = null;

    protected $table = 'wechat_luckydraw_01_gather';
    protected $fillable = ['openid', 'token', 'phone', 'idcard', 'name', 'l_id'];

    public function scopeFansFind($query, $token, $l_id, $openid)
    {
        $query->where([
            'token' => $token,
            'l_id' => $l_id,
            'openid' => $openid
        ]);
    }

}
