<?php

namespace App\Models\Vote;

use Illuminate\Database\Eloquent\Model;

class VoteMessage extends Model
{

    const UPDATED_AT = null;

    const CREATED_AT = 'create_at';

    protected $table = 'w_vote_message';

    protected $fillable = ['a_id', 'g_id', 't_id', 'f_id', 'openid', 'content', 'status'];

    public function fans()
    {
        return $this->hasOne('App\Models\Wechat\Fans', 'id', 'f_id');
    }
}
