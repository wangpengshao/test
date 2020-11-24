<?php

namespace App\Models\Vote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class VoteConfig extends Model
{
    const UPDATED_AT = null;

    protected $table = 'w_vote_config';

    protected $casts = [
        'warning_rule' => 'json',
        'lock_rule' => 'json',
        'img' => 'json',
    ];

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getShareImgAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    public function scopeGetCache($query, $token, $id)
    {
        $cacheKey = 'vote:conf:' . $token . ':' . $id;
        $voteConfig = Cache::get($cacheKey);
        if (empty($voteConfig)) {
            $voteConfig = $query->where(['id' => $id, 'token' => $token])->first();
            if ($voteConfig) {
                Cache::put($cacheKey, $voteConfig, 60);
            } else {
                return false;
            }
        }
        return $voteConfig;
    }

}
