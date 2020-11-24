<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OtherConfig 微门户拓展其它配置项
 * @package App\Models\Wechat
 */
class OtherConfig extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;
    /**
     * @var string
     */
    protected $table = 'admin_wxuser_other';


    public function scopeMiniResources($query, $wxuser_id)
    {
        return $query->where('wxuser_id', $wxuser_id)
            ->select('mn_resources_appid', 'mn_resources_key', 'mn_resources_sw');
    }

    public function scopeOtherCustom($query, $wxuser_id)
    {
        return $query->where('wxuser_id', $wxuser_id)
            ->select('vue_nav_sw', 'appointment_min_day', 'appointment_max_day');
    }

    public function scopeUnion($query, $wxuser_id)
    {
        return $query->where('wxuser_id', $wxuser_id)
            ->select('union_url');
    }

    public function scopeGetPwdConfig($query, $wxuser_id)
    {
        return $query->where('wxuser_id', $wxuser_id)
            ->select('pw_check_sw', 'pw_min_length', 'pw_max_length', 'pw_type', 'pw_prompt');
    }

}
