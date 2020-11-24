<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class IndexMenu extends Model
{
    protected $table = 'admin_wechat_index_menu';


    protected $casts = [
        'extra' => 'json',
    ];

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getIconAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    public function getAddInfoAttribute($options)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        return $options;
    }

    public function setAddInfoAttribute($options)
    {
        if (is_array($options)) {
            $options = join(',', $options);
        }
        $this->attributes['add_info'] = $options;
    }

    public function relevance()
    {
        return $this->hasOne(RelevanceMenu::class, 'id', 'r_id');
    }

    public function scopeGetCache($query, $token, $id, $minute = 60)
    {
        $cacheKey = sprintf(config('cacheKey.vueMenu'), $token, $id);
        $cache = Cache::get($cacheKey);
        if ($cache == null) {
            $cache = $query->where(['token' => $token, 'id' => $id])->first();
            if (empty($cache)) return false;
            Cache::put($cacheKey, $cache, $minute);
        }
        return $cache;
    }

    public function scopeVueCache($query, $token, $minute = 60)
    {
        $cacheKey = sprintf(config('cacheKey.vueShowMenu'), $token);
        $cache = Cache::get($cacheKey);
        if (empty($cache)) {
            $cache = $query->where([
                'token' => $token,
                'status' => 1
            ])->orderBy('order', 'desc')
                ->get(['flag', 'flagColor', 'icon', 'caption', 'id'])->toArray();
            if ($cache) {
                Cache::put($cacheKey, $cache, $minute);
            }
        }
        return $cache;
    }

}
