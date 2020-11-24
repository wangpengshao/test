<?php

namespace App\Models\CollectCard;

use App\Models\Wechat\Fans;
use App\Models\Wechat\Reader;
use Illuminate\Database\Eloquent\Model;

class CollectUsers extends Model
{
    const UPDATED_AT = null;

    protected $table = 'w_collect_card_u';

    protected $fillable = [
        'a_id', 'token', 'origin_type', 'created_at', 'openid', 'last_at', 'collect_all', 'ok_at', 'parent_id', 'origin_id'
    ];

//    public function hasManyLog()
//    {
//        return $this->hasMany(CollectLog::class, 'user_id', 'id')
//            ->orderBy('created_at', 'DESC')->select('task_id', 'created_at', 'task_type', 'id', 'user_id');
//    }

    public function scopeGetUser($query, $openid, $where)
    {
        return $query->where('openid', $openid)->where($where);
    }

    public function hasOneFansInfo()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid')->select(['openid', 'nickname', 'headimgurl']);
    }

    public function hasOneReader()
    {
        return $this->hasOne(Reader::class, 'openid', 'openid')->select(['openid', 'rdid', 'name']);
    }

    public function hasOneOrigin()
    {
        return $this->hasOne(CollectTask::class, 'id', 'origin_id')->select(['id', 'title']);
    }

    public function hasOneReward()
    {
        return $this->hasOne(CollectReward::class, 'user_id', 'id');
    }

}
