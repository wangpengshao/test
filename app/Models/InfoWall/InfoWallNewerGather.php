<?php

namespace App\Models\InfoWall;

use Illuminate\Database\Eloquent\Model;

class InfoWallNewerGather extends Model
{
    const UPDATED_AT = null;

    protected $table = 'w_infowall_newer_gather';
    protected $fillable = ['openid', 'token', 'phone', 'idcard', 'name', 'l_id'];

    public function scopeFansFind($query, $token, $l_id, $openid)
    {
        $query->where([
            'token' => $token,
            'l_id' => $l_id,
            'openid' => $openid
        ]);
    }

}
