<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CollectTask extends Model
{
    const UPDATED_AT = null;

    protected $casts = [
        'sub_data' => 'json'
    ];

    protected $table = 'w_collect_card_t';

    protected $fillable = [
        'a_id', 'token', 'user_id', 'c_id', 'type', 'giver', 'isValid', 'task_type',
        'task_id', 'origin_type', 'task_serial'
    ];

    public function hasOneCard()
    {
        return $this->hasOne(CardConfig::class, 'id', 'c_id');
    }

    public function scopeGetAllCache($query, $token, $aid)
    {
        $cacheKey = 'collectCard:task:' . $token . ':' . $aid;
        $collectTask = Cache::get($cacheKey);
        if (empty($collectTask)) {
            $collectTask = $query->where(['a_id' => $aid, 'token' => $token, 'status' => 1])->get();
            if (!$collectTask->isEmpty()) {
                Cache::put($cacheKey, $collectTask, 300);
            } else {
                return collect();
            }
        }
        return $collectTask;
    }

    public function scopeFindOrigin($query, $where, $origin_type, $origin_id)
    {
        return $query->where($where)->where(['origin_type' => $origin_type, 'origin_id' => $origin_id]);
    }
}
