<?php

namespace App\Models\InfoWall;

use Illuminate\Database\Eloquent\Model;

class InfoWallNewsList extends Model
{
    protected $table = 'w_infowall_news_list';

    protected $fillable = [
        'rdid', 'text', 'openid', 'content', 'status', 'token', 'gather_id',
        'l_id', 'updated_at', 'created_at', 'nickname', 'is_shelf'
    ];

    public function scopeCurrent($query, $token, $id)
    {
        return $query->where([
            'id' => $id,
            'token' => $token,
        ]);
    }

    public function hasOneUser()
    {
        return $this->hasOne(InfoWallUserInfo::class, 'id', 'user_id');
    }

    public function hasOneAct()
    {
        return $this->hasOne(InfoWallConfig::class, 'id', 'l_id');
    }

}
