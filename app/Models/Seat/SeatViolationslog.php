<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class SeatViolationslog extends Model
{
    protected $table = 'seat_violations_log';

    public $timestamps = false;

    protected $fillable = ['token', 'rdid', 'type', 'scan_id', 'booking_id', 'mark', 'created_at'];
}
