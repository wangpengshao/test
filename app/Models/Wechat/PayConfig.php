<?php

namespace App\Models\Wechat;

use App\Models\Wxuser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class PayConfig 微门户默认商户号配置
 * @package App\Models\Wechat
 */
class PayConfig extends Model
{
    /**
     * @var string
     */
    protected $table = 'admin_wxuser_payconfig';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wxuser()
    {
        return $this->belongsTo(Wxuser::class);
    }

    /**
     * @param $query
     * @param $token
     * @return mixed
     */
    public function scopeGetCache($query, $token)
    {
        $cacheKey = sprintf(config('cacheKey.wxuserPayConf'), $token);
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $wxuser_id = Wxuser::where('token', $token)->value('id');
            if ($wxuser_id === null) {
                abort(500, 'invalid token');
            }
            $config = $query->where('wxuser_id', $wxuser_id)->first();
            if ($config) {
                $cache = $config;
                Cache::put($cacheKey, $config, 60);
            }
        }
        return $cache;
    }
}
