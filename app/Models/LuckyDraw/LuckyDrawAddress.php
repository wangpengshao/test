<?php

namespace App\Models\LuckyDraw;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LuckyDrawAddress extends Model
{
    protected $table = 'w_luckydraw_address';
    protected $fillable = [
        'p_id', 'draw_type', 'name', 'phone', 'address', 'token'
    ];

}
