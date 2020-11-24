<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class SeatAttr extends Model
{
    protected $table = 'seat_attr';

    public $timestamps = false;

    public function attr()
    {
        return $this->belongsToMany('App\Models\Seat\SeatChart','seat_chart_attr','attr_id','chart_id');
    }
}
