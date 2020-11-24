<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class SeatCurrBooking extends Model
{
    protected  $table = 'seat_curr_booking';

    public function region()
    {
        return $this->belongsTo('App\Models\Seat\SeatRegion','region_id');
    }

    public function chart()
    {
        return $this->belongsTo('App\Models\Seat\SeatChart','chart_id');
    }
}
