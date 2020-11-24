<?php

namespace App\Models\Seat;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'seat_config';

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = ['token','created_at'];
}
