<?php

namespace App\Models\InfoWall;

use Illuminate\Database\Eloquent\Model;

class InfoWallUserInfo extends Model
{
    protected $table = 'w_infowall_userinfo';

    protected $fillable = [
        'token', 'l_id', 'username', 'nickname', 'rdid', 'openid', 'headimgurl',
        'sex', 'phone', 'address', 'created_at'
    ];

    public function scopeCurrent($query, $token, $id)
    {
        return $query->where([
            'id' => $id,
            'token' => $token,
        ]);
    }

    public function hasManyNews()
    {
        return $this->hasMany(InfoWallNewsList::class, 'user_id', 'id');
    }

    public function hasOneAct()
    {
        return $this->hasOne(InfoWallConfig::class, 'id', 'l_id');
    }

}
