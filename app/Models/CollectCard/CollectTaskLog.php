<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;

class CollectTaskLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'w_collect_card_t_log';

    protected $fillable = [
        'a_id', 'token', 'user_id', 't_id', 'created_at', 'rdid', 'token'
    ];

}
