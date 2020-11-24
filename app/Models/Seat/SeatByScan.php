<?php

namespace App\Models\Seat;

use App\Models\Wechat\Fans;
use Illuminate\Database\Eloquent\Model;

class SeatByScan extends Model
{
    protected $table = 'seat_by_scan';

    protected $fillable = ['token','openid','rdid','chart_id','s_time','e_time','mark'];

    public function chart()
    {
        return $this->belongsTo('App\Models\Seat\SeatChart','chart_id','id');
    }

    public function fans()
    {
        return $this->belongsTo(Fans::class,'openid','openid');
    }
}
