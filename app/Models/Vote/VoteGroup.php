<?php

namespace App\Models\Vote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class VoteGroup extends Model
{

    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $table = 'w_vote_group';

//    public $sortable = [
//        'order_column_name' => 'sort',
//        'sort_when_creating' => true,
//    ];

    public function fields()
    {
        return $this->hasMany(VoteField::class, 'g_id');
    }

    public function scopeGetCache($query, $token, $a_id)
    {
        $cacheKey = 'vote:group:' . $token . ':' . $a_id;
        $voteGroup = Cache::get($cacheKey);
        if (empty($voteGroup)) {
            $voteGroup = $query->where(['a_id' => $a_id, 'token' => $token])->get();
            if ($voteGroup) {
                Cache::put($cacheKey, $voteGroup, 60);
            } else {
                return false;
            }
        }
        return $voteGroup;
    }

}
