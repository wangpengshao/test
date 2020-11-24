<?php

namespace App\Models\Notice;

use Illuminate\Database\Eloquent\Model;

class ExpireNotice extends Model
{
    protected $table = 'w_expire_notice_conf';

    protected $casts = [
        'te1_da' => 'json',
    ];
}
