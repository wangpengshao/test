<?php

namespace App\Models\IntegralExchange;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IntegralExchangePrize extends Model
{
    const CREATED_AT = null;

    protected $table = 'wechat_integral_exchange_prize';

    public $fillable = [
        'token', 'type', 'image', 'title', 'inventory', 'integral', 'money'
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

    public function scopeCurrent($query, $token, $id)
    {
        return $query->where([
            'token' => $token,
            'id' => $id
        ]);
    }


}
