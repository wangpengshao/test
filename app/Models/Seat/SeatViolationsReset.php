<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class SeatViolationsReset extends Model
{
    protected $table = 'seat_violations_reset';

    public $timestamps = false;

    protected $fillable = ['token', 'rdid', 'created_at'];
}
