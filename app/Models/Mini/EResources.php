<?php

namespace App\Models\Mini;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EResources extends Model
{
    const UPDATED_AT = null;

    protected $table = 'mini_e_resources_c';

    public function scopeGetCache($query, $token = '', $minute = 30)
    {
        $cacheKey = 'mini:eResources:' . $token;
        $first = Cache::get($cacheKey);
        if (empty($first)) {
            $first = $query->where('token', $token)->first();
            if (isset($first)) {
                Cache::put($cacheKey, $first, $minute);
            } else {
                $first = false;
            }
        }
        return $first;
    }
}
