<?php

namespace App\Models\Vote;

use App\Models\Wechat\Fans;
use Illuminate\Database\Eloquent\Model;

class VoteBlacklist extends Model
{
    const UPDATED_AT = null;

    protected $table = 'w_vote_blacklist';

    public function fansInfo()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid');
    }

    public function getIpAttribute($ip)
    {
        if ($ip) {
            return long2ip($ip);
        }
        return '';
    }

    public function setIpAttribute($ip)
    {
        if ($ip) {
            $this->attributes['ip'] = ip2long($ip);
        }
    }
}
