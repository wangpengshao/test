<?php

namespace App\Models\LuckyDraw;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LuckyPrize extends Model
{
    const CREATED_AT = null;

    protected $table = 'wechat_luckydraw_prize';

    public $fillable = [
        'token', 'type', 'image', 'title', 'inventory', 'weight', 'integral', 'money', 'l_id', 'lucky_type'
    ];

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


}
