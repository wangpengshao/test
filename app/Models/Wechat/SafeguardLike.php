<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class SafeguardLike extends Model
{
    protected $table = 'w_safeguard_like';
    protected $fillable = [
        'token', 'openid', 'like'
    ];

    public function scopeMy($query, $token, $openid)
    {
        $where = [
            'token' => $token,
            'openid' => $openid
        ];
        $first = $query->where($where)->first();
        if ($first == null) {
            $where['like'] = '';
            $first = $query->create($where);
        }
        return $first;
    }

    public function getLikeAttribute($options)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        return $options;
    }

    public function setLikeAttribute($options)
    {
        if (is_array($options)) {
            $options = join(',', $options);
        }
        $this->attributes['like'] = $options;
    }

}
