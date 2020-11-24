<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HtmlConfig extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $table = 'w_collect_card_html_c';

    public function scopeGetCache($query, $token, $aid)
    {
        $cacheKey = 'collectCard:html:' . $token . ':' . $aid;
        $htmlConfig = Cache::get($cacheKey);
        if (empty($htmlConfig)) {
            $htmlConfig = $query->where(['a_id' => $aid, 'token' => $token])->first();
            if ($htmlConfig) {
                Cache::put($cacheKey, $htmlConfig, 300);
            } else {
                return false;
            }
        }
        return $htmlConfig;

    }
}
