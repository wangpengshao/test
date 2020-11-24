<?php

namespace App\Models\Recommend;

use Illuminate\Database\Eloquent\Model;

class MessageList extends Model
{
    protected $table = 'w_recommend_m';

    protected $fillable = ['r_reply', 'a_reply', 'm_id', 'token', 's_id', 'r_id', 'is_reading'];


    public function hasOneBook()
    {
        return $this->hasOne(RecommendBooks::class, 'id', 'm_id');
    }

}
