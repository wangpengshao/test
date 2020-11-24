<?php

namespace App\Models\Seat;

use App\Models\Wechat\Fans;
use Illuminate\Database\Eloquent\Model;

class SeatUser extends Model
{
    protected $table = 'seat_users';

    protected $fillable = ['token', 'openid', 'rdid', 'last_date', 'status', 'violations', 'forbidden'];

    public function scopeGetUser($query,$token,$rdid)
    {
        return $query->where([
            'token' => $token,
            'rdid' => $rdid,
        ]);
    }

    public function fans()
    {
        return $this->belongsTo(Fans::class,'openid','openid');
    }
}
