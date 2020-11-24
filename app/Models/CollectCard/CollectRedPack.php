<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;

class CollectRedPack extends Model
{
    protected $table = 'w_collect_card_redpack';
    const UPDATED_AT = null;
    protected $fillable = [
        'p_id', 'money', 'status', 'isValid', 'created_at', 'update_at'
    ];

}
