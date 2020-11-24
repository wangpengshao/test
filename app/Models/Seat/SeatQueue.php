<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class SeatQueue extends Model
{
    protected $table = 'seat_queue';

    protected $fillable = ['token', 'openid', 'rdid', 'chart_id', 'status','seating_time'];
}
