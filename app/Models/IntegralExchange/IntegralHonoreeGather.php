<?php

namespace App\Models\IntegralExchange;

use Illuminate\Database\Eloquent\Model;

class IntegralHonoreeGather extends Model
{
    const UPDATED_AT = null;

    protected $table = 'wechat_integral_honoree_gather';
    protected $fillable = ['openid', 'token', 'phone', 'address', 'name'];

    public function scopeFansFind($query, $token, $openid)
    {
        $query->where([
            'token' => $token,
            'openid' => $openid
        ]);
    }

}
