<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Imagewechat extends Model
{
    protected $table = 'admin_wechat_images';

    protected $fillable = [
        'status', 'order', 'caption', 'image', 'token', 'url'
    ];

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getImageAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    public function scopeVueCache($query, $token, $minutes = 30)
    {
        $cacheKey = 'vueIndex:img:' . $token;
        $cache = Cache::get($cacheKey);
        if (empty($cache)) {
            $cache = $query->where([
                'token' => $token,
                'status' => 1
            ])->orderBy('order', 'desc')->get(['caption', 'image', 'url'])->toArray();
            if ($cache) {
                Cache::put($cacheKey, $cache, $minutes);
            }
        }
        return $cache;
    }
}
