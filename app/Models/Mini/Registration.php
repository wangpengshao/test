<?php

namespace App\Models\Mini;

use App\Models\Mini\PayConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Registration extends Model
{
    const UPDATED_AT = null;

    protected $table = 'mini_registration_c';

//    protected $casts = [
//        'password_limit' => 'json',
//    ];

    public function scopeGetCache($query, $token = '', $minute = 30)
    {
        $cacheKey = 'mini:register:' . $token . ':c';
        $first = Cache::get($cacheKey);
        if (empty($first)) {
            $first = $query->where('token', $token)
                ->with(['hasManyType' => function ($query) {
                    $query->where('status', '=', '1')->orderBy('order', 'desc');
                }, 'hasManyImg' => function ($query) {
                    $query->where('status', '=', '1');
                }])->first();
            if (isset($first)) {
                Cache::put($cacheKey, $first, $minute);
            } else {
                $first = false;
            }
        }
        return $first;
    }

    public function hasManyType()
    {
        return $this->hasMany(RegistrationType::class, 'token', 'token');
    }

    public function hasManyImg()
    {
        return $this->hasMany(RegistrationImg::class, 'token', 'token');
    }

    public function getOpacurlAttribute($value)
    {
        if ($value) {
            return str_finish($value, '/');
        }
    }

    public function getOpenlibUrlAttribute($value)
    {
        if ($value) {
            return str_finish($value, '/');
        }

    }

    public function getColorsAttribute($extra)
    {
        return array_values(json_decode($extra, true) ?: []);
    }

    public function setColorsAttribute($extra)
    {
        $this->attributes['colors'] = json_encode(array_values($extra));
    }

    /**
     * 关联-微信支付配置
     */
    public function payconfig()
    {
        return $this->hasOne(PayConfig::class, 'mini_id');
    }
}
