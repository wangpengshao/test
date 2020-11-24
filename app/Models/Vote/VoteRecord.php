<?php

namespace App\Models\Vote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VoteRecord extends Model
{

    const UPDATED_AT = null;

    protected $table = 'w_vote_record';

    protected $fillable = ['a_id', 't_id', 'rdid', 'openid', 'ip', 'area', 'create_at', 'g_id'];

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
