<?php

namespace App\Models\Vote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class VoteItems extends Model
{

    const UPDATED_AT = null;

    protected $table = 'w_vote_items';

    protected $fillable = ['a_id', 'g_id', 'rdid', 'openid', 'title', 'phone', 'cover', 'info',
        'view_n', 'voting_n', 'ranking', 'content', 'status', 'lockinfo', 'lockstatus', 'number'];

    protected $casts = [
        'content' => 'json'
    ];

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getCoverAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    public function scopeAllItems($query, $a_id, $g_id, $column = [])
    {
//        ['id', 'cover', 'number', 'title']
        $cacheKey = 'vote:itemAll:a:' . $a_id . ':g:' . $g_id;
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $where = [
                'a_id' => $a_id,
                'g_id' => $g_id,
                'status' => 1
            ];
            $cache = $query->where($where)->get();
            Cache::put($cacheKey, $cache, 30);
        }
        if ($column) {
            $cache = $cache->map(function ($value, $key) use ($column) {
                return $value->only($column);
            });
        }
        return $cache;
    }

    public function scopeFindOpenid($query, $a_id, $g_id, $openid)
    {
        return $query->where([
            'g_id' => $g_id,
            'openid' => $openid,
            'a_id' => $a_id,
        ]);
    }

//    public function getContentAttribute($content)
//    {
//        return array_values(json_decode($content, true) ?: []);
//    }

//    public function setContentAttribute($content)
//    {
//        $this->attributes['content'] = json_encode(array_values($content));
//    }

    public function fans()
    {
        return $this->hasOne('App\Models\Wechat\Fans', 'openid', 'openid');
    }
}
