<?php

namespace App\Models\Vote;

use Illuminate\Database\Eloquent\Model;

class VoteField extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $table = 'w_vote_field';

    public $fillable = ['name', 'type', 'g_id','data','required_sw','show_sw'];

    public function group()
    {
        return $this->belongsTo(VoteGroup::class, 'id', 'g_id');
    }


}
