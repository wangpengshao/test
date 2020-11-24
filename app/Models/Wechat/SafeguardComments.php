<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SafeguardComments extends Model
{
    protected $table = 'w_safeguard_comments';
    protected $fillable = [
        'openid', 'token', 'nickname', 'headimgurl', 'content', 'like_n', 'status'
    ];

    public function getCreatedAtAttribute($created_at)
    {
        $time = strtotime($created_at);
        if ($time - time() < 2505600) {
            $created_at = Carbon::parse($created_at)->diffForHumans();
        }
        return $created_at;
    }

    public function scopeChangeLike($query, $id, $num)
    {
        if ($num > 0) {
            return $query->find($id)->increment('like_n', $num);
        }
        return $query->find($id)->decrement('like_n', abs($num));
    }
}
