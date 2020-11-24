<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Bindweb extends Model
{
    protected $table = 'admin_wechat_web_bind';

    public function scopeGetCache($query, $token)
    {
        $cacheKey = sprintf(config('cacheKey.vueBindConf'), $token);
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $cache = [
                'title' => '读者账号绑定',
                'content' => '',
                'uname' => '读者账号',
                'uremark' => '请输入读者账号',
                'pname' => '读者密码',
                'premark' => '请输入读者密码',
                'l_title' => '',
                'l_link' => '',
                'r_title' => '',
                'r_link' => '',
            ];
            $first = $query->where('token', $token)->first();
            if ($first && $first['status'] === 1) {
                foreach ($cache as $k => $v) {
                    $val = $first[$k];
                    if (!empty($val)) {
                        $cache[$k] = $val;
                    }
                }
            }
            Cache::put($cacheKey, $cache, 60);
        }
        return $cache;
    }

}
