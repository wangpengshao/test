<?php

namespace App\Models\Card;

use Illuminate\Database\Eloquent\Model;

class MemberCardUser extends Model
{
    protected $table = 'wechat_membercard_users';

    protected $dateFormat = 'U';

    protected $fillable = ['token', 'card_id', 'from', 'openid', 'nickName', 'avatarUrl', 'gender', 'country', 'province', 'city', 'code', 'rdid'];
}
