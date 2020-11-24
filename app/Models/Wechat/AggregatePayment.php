<?php

namespace App\Models\Wechat;

use App\Models\Wxuser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AggregatePayment extends Model
{
    protected $table = 'admin_wxuser_aggregate_payment';

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
        $cacheKey = 'wechat.aggregatePayment.payConf:'. $token;
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $wxuser_id = Wxuser::where('token', $token)->value('id');
            if ($wxuser_id === null) {
                abort(500, 'invalid token');
            }
            $config = $query->where('wxuser_id', $wxuser_id)->first();
            if ($config) {
                $config['icbc_private_key'] = file_get_contents(Storage::disk('admin')->url($config['icbc_private_key']));
                $config['icbc_public_key'] = file_get_contents(Storage::disk('admin')->url($config['icbc_public_key']));
                $config['icbc_geteway_publickey'] = file_get_contents(Storage::disk('admin')->url($config['icbc_geteway_publickey']));

                $cache = $config;
                Cache::put($cacheKey, $config, 60);
            }
        }
        return $cache;
    }
}
