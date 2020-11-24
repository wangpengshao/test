<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class SeatChart extends Model
{
    protected $table = 'seat_chart';

    protected $fillable = ['token','region_id','numid'];

    public function attr()
    {
        return $this->belongsToMany('App\Models\Seat\SeatAttr','seat_chart_attr','chart_id','attr_id');
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Seat\SeatRegion','region_id','id');
    }

    public function queue()
    {
        return $this->belongsTo('App\Models\Seat\SeatQueue','queue_id','id');
    }
}
