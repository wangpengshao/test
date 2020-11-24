<?php

namespace App\Models\specialColumn;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class RedGevemment extends Model
{
    protected $table = 'web_redgevemment';

    public function getLogoAttribute($img)
    {
        if ($img) {
            return Storage::disk(config('admin.upload.disk'))->url($img);
        }
        return '';
    }

    public function scopeGetCache($query, $token, $minute=30)
    {
        $cacheKey = sprintf('Resource.%s:c', $token);
        $first = Cache::get($cacheKey);
        if($first == null){
            $first = $query->where('token', $token)
                ->first(['logo','name','status','date_start','date_end']);
            if (isset($first)) {
                $first = $first->toArray();
                Cache::put($cacheKey, $first, $minute);
            } else {
                $first = false;
            }
        }

        return $first;
    }

}
