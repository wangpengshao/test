<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;

class CollectReward extends Model
{
    const UPDATED_AT = null;

    protected $table = 'w_collect_card_u_reward';

    protected $fillable = ['a_id', 'token', 'type', 'user_id', 'prize_id', 'is_get',
        'redpack_id', 'get_at', 'status', 'redpack_log'];

    public function scopeGetReward($query, $user_id, $where)
    {
        return $query->where('user_id', $user_id)->where($where);
    }

    public function hasOnePrize()
    {
        return $this->hasOne(CollectPrize::class, 'id', 'prize_id');
    }

    public function hasOneRedPage()
    {
        return $this->hasOne(CollectRedPack::class, 'id', 'redpack_id')->select('money', 'id');
    }
}
