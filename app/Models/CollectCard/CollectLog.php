<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CollectLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'w_collect_card_log';

    protected $fillable = [
        'a_id', 'token', 'user_id', 'c_id', 'giver', 'isValid', 'origin_type',
        'origin_id', 'is_expend'
    ];

    public function hasOneCard()
    {
        return $this->hasOne(CardConfig::class, 'id', 'c_id');
    }

    public function scopeGroupCount($query, $user_id, $where)
    {
        return $query->where(['user_id' => $user_id, 'isValid' => 1])
            ->where($where)->groupBy('c_id')
            ->select(DB::raw("count(1) as count"), 'c_id')
            ->pluck('count', 'c_id');
    }

    public function scopeGroupFirstId($query, $user_id, $where)
    {
        return $query->where(['user_id' => $user_id, 'isValid' => 1])
            ->where($where)->groupBy('c_id')
            ->select('id', 'c_id')
            ->pluck('id', 'c_id');
    }

    public function scopeGetMyLog($query, $user_id, $where, $order = 'ASC')
    {
        return $query->where(['user_id' => $user_id, 'isValid' => 1])
            ->where($where)->orderBy('created_at', $order);
    }

    public function scopeGetMyAllLog($query, $user_id, $where, $order = 'ASC')
    {
        return $query->where(['user_id' => $user_id])->where($where)->orderBy('created_at', $order);
    }

    public function scopeGetTaskLog($query, $where, $user_id, $origin_type, $origin_id)
    {
        return $query->where($where)->where([
            'user_id' => $user_id,
            'origin_type' => $origin_type,
            'origin_id' => $origin_id
        ]);
    }
}
