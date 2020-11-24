<?php

namespace App\Models\Notice;

use Illuminate\Database\Eloquent\Model;

class NoticeRecord extends Model
{
    protected $table = 'w_expire_notice_record';

    protected $casts = [
        'info' => 'json',
    ];

}
