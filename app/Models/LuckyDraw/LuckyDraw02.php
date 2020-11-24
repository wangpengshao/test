<?php

namespace App\Models\LuckyDraw;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LuckyDraw02 extends Model
{
    const CREATED_AT = null;

    protected $table = 'wechat_luckydraw_02';

    public function scopeCurrent($query, $token, $id)
    {
        return $query->where([
            'id' => $id,
            'token' => $token,
        ]);
    }

    public function hasManyPrize()
    {
        return $this->hasMany(LuckyPrize::class, 'l_id', 'id')->where('lucky_type', 2);
    }

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getImageAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    public function getGatherAttribute($options)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        return $options;
    }

    public function setGatherAttribute($options)
    {
        if (is_array($options)) {
            $options = join(',', $options);
        }
        $this->attributes['gather'] = $options;
    }

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getShareImgAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

}
