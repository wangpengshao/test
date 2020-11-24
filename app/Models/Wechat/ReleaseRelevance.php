<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class ReleaseRelevance extends Model
{
    protected $table = 'w_release_relevance';

    public $timestamps = false;

    protected $fillable = [
        'r_id', 'data_id', 'token'
    ];

}
