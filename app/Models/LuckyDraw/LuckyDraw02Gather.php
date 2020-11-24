<?php

namespace App\Models\LuckyDraw;

use Illuminate\Database\Eloquent\Model;

class LuckyDraw02Gather extends Model
{
    const UPDATED_AT = null;

    protected $table = 'wechat_luckydraw_02_gather';
    protected $fillable = ['openid', 'token', 'phone', 'idcard', 'name', 'l_id'];

}
