<?php

namespace App\Models;

use App\Models\Mini\EResources;
use App\Models\Wechat\Address;
use App\Models\Wechat\AggregatePayment;
use App\Models\Wechat\OtherConfig;
use App\Models\Wechat\PayConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/**
 * Class Wxuser
 * @package App\Models
 */
class Wxuser extends Model
{
    /**
     * @var string
     */
    protected $table = 'admin_wxuser';

    /**
     * @param $query
     * @param $array
     * @return mixed
     */
    public function scopeIsuser($query, $array)
    {
        return $query->where(['user_id' => $array[0], 'id' => $array[1]]);
    }

    /**
     * @param        $query
     * @param string $token
     * @return array
     */
    public function scopeGetConfig($query, $token = '')
    {
        $cacheKey = sprintf(config('cacheKey.wxuserConf'), $token);
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $config = $query->where('token', $token)->first(['appid', 'appsecret', 'keytoken', 'aes_key', 'old_token']);
            if ($config) {
                $cache = [
                    'app_id' => $config['appid'],
                    'secret' => $config['appsecret'],
                    'token' => $config['keytoken'],
                    'aes_key' => $config['aes_key'],
                    'old_token' => $config['old_token']
                ];
                Cache::put($cacheKey, $cache, 60);
            } else {
                abort(500, 'invalid token');
            }
        }
        return $cache;
    }

    /**
     * @param        $query
     * @param string $token
     * @return mixed|string
     */
    public function scopeGetCache($query, $token = '')
    {
        $cacheKey = sprintf(config('cacheKey.wxuser'), $token);
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $first = $query->where('token', $token)->first();
            if ($first) {
                $cache = $first;
                Cache::put($cacheKey, $first, 60);
            } else {
                return false;
            }
        }
        return $cache;
    }

    /**
     * @param        $query
     * @param string $token
     * @return mixed
     */
    public function scopeTokenfind($query, $token = '')
    {
        return $query->where('token', $token);
    }

    /**
     * @param $value
     * @return string
     */
    public function getOpacurlAttribute($value)
    {
        if ($value) {
            return Str::finish($value, '/');
        }

    }

    /**
     * @param $value
     * @return string
     */
    public function getActivityUrlAttribute($value)
    {
        if ($value) {
            return Str::finish($value, '/');
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function getOpcsUrlAttribute($value)
    {
        if ($value) {
            return Str::finish($value, '/');
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function getSsoUrlAttribute($value)
    {
        if ($value) {
            return Str::finish($value, '/');
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function getSmsUrlAttribute($value)
    {
        if ($value) {
            return Str::finish($value, '/');
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function getUshopUrlAttribute($value)
    {
        if ($value) {
            return Str::finish($value, '/');
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function getKnowledgeUrlAttribute($value)
    {
        if ($value) {
            return Str::finish($value, '/');
        }
    }

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getHeaderpicAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getQrCodeAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }


    /**
     * 关联-公众号-省市区位置
     */
    public function address()
    {
        return $this->hasOne(Address::class);
    }


    /**
     * 关联-微信支付配置
     */
    public function payconfig()
    {
        return $this->hasOne(PayConfig::class);
    }

    /**
     * 关联-银行微信聚合支付配置
     */
    public function aggregatepayment()
    {
        return $this->hasOne(AggregatePayment::class);
    }

    /**
     * 关联-拓展配置项
     */
    public function otherconfig()
    {
        return $this->hasOne(OtherConfig::class);
    }

    /**
     * 关联-拓展配置项
     */
    public function miniresources()
    {
        return $this->hasOne(EResources::class, 'token', 'token');
    }
}
