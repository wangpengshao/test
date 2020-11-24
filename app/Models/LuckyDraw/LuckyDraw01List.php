<?php

namespace App\Models\LuckyDraw;

use App\Models\Wechat\Fans;
use Illuminate\Database\Eloquent\Model;

class LuckyDraw01List extends Model
{

    protected $table = 'wechat_luckydraw_01_list';

    protected $fillable = [
        'rdid', 'text', 'openid', 'is_winning', 'code', 'status', 'token', 'gather_id', 'prize_id',
        'l_id', 'updated_at', 'created_at', 'nickname'
    ];

    public function fansInfo()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid')->select(['openid', 'nickname', 'headimgurl']);
    }

    public function prize()
    {
        return $this->hasOne(LuckyPrize::class, 'id', 'prize_id')->select(['id', 'title', 'image', 'type']);
    }

    // 根据draw_type字段区分抽奖类型对应的地址信息
    public function address()
    {
        return $this->hasOne(LuckyDrawAddress::class, 'p_id', 'id')->where('draw_type', 1);;
    }

    public function luckyDraw()
    {
        return $this->hasOne(LuckyDraw01::class, 'id', 'l_id')->select(['id', 'title']);
    }


    public function scopeIsWinning($query, $is_winning)
    {
        if (!in_array($is_winning, ['0', '1', 'all'])) {
            return $query;
        }

        if ($is_winning != 'all') {
            $query->where('is_winning', $is_winning);
        }
    }

    public function scopeUserWinning($query, $token, $l_id)
    {
        $query->where([
            'token' => $token,
            'l_id' => $l_id,
            'is_winning' => 1
        ]);
    }

    public function hasOneGather()
    {
        return $this->hasOne(LuckyDraw01Gather::class, 'id', 'gather_id');
    }

}
