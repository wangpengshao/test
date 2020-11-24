<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CollectCard extends Model
{
    protected $table = 'w_collect_card';

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

    public function hasManyCard()
    {
        return $this->hasMany(CardConfig::class, 'a_id', 'id')->where('status', 1)->orderBy('order');
    }

    public function hasHtmlConfig()
    {
        return $this->hasOne(HtmlConfig::class, 'a_id', 'id');
    }

    public function hasManyShowTask()
    {
        return $this->hasMany(CollectTask::class, 'a_id', 'id')
            ->where(['status' => 1, 'is_show' => 1])
//            ->whereNotNull('menu_id')
            ->select(['id', 'title', 'info', 'type', 'origin_type', 'origin_id']);
    }

    public function hasManyTask()
    {
        return $this->hasMany(CollectTask::class, 'a_id', 'id')->where('status', 1);
    }

    public function scopeGetCache($query, $token, $id)
    {
        $cacheKey = 'collectCard:conf:' . $token . ':' . $id;
        $collectCard = Cache::get($cacheKey);
        if (empty($collectCard)) {
            $collectCard = $query->where(['id' => $id, 'token' => $token])->first();
            if ($collectCard) {
                Cache::put($cacheKey, $collectCard, 60);
            } else {
                return false;
            }
        }
        return $collectCard;
    }

}
