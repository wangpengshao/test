<?php

namespace App\Models\IntegralExchange;

use App\Models\Wechat\Fans;
use Illuminate\Database\Eloquent\Model;

class IntegralExchangePrizeList extends Model
{

    protected $table = 'wechat_integral_exchange_prize_list';

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
        return $this->hasOne(IntegralExchangePrize::class, 'id', 'prize_id')->select(['id', 'title', 'image', 'type']);
    }

}
