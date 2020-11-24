<?php

namespace App\Models\Mini;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class PayConfig 小程序默认商户号配置
 * @package App\Models\Wechat
 */
class PayConfig extends Model
{
    /**
     * @var string
     */
    protected $table = 'admin_mini_payconfig';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class, 'mini_token');
    }

    /**
     * @param $query
     * @param $token
     * @return mixed
     */
    public function scopeGetCache($query, $token)
    {
        $cacheKey = sprintf(config('cacheKey.miniPayConf'), $token);
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $mini_id = Registration::where('token', $token)->value('id');
            if ($mini_id === null) {
                abort(500, 'invalid token');
            }
            $config = $query->where('mini_id', $mini_id)->first();
            if ($config) {
                $cache = $config;
                Cache::put($cacheKey, $config, 60);
            }
        }
        return $cache;
    }
}
