<?php

namespace App\Models\Seat;

use App\Models\Wechat\Fans;
use Illuminate\Database\Eloquent\Model;

class SeatByBooking extends Model
{
    protected $table = 'seat_by_booking';

    protected $fillable = [
        'token', 'openid', 'rdid', 'chart_id', 's_time', 'e_time', 'status', 'from', 'sign_min', 'sign_max', 'sign_in', 'mark', 'created_at', 'updated_at'
    ];

    public function chart()
    {
        return $this->belongsTo('App\Models\Seat\SeatChart','chart_id');
    }

    public function fans()
    {
        return $this->belongsTo(Fans::class,'openid','openid');
    }
}
