<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class SeatScoreLog extends Model
{
    protected $table = 'seat_scorelog';

    public $timestamps = false;

    protected $fillable = ['token', 'user_id', 'status', 'c_time', 'num', 'blog_id', 'log_id', 'text'];

}
