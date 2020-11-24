<?php

namespace App\Models\Wechat;

use Illuminate\Notifications\Notifiable;
//use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

class Fans extends Authenticatable
{
//    use Notifiable,HasApiTokens; HasMultiAuthApiTokens
    use Notifiable, HasMultiAuthApiTokens;

    protected $table = 'admin_wechat_fans';

    protected $fillable = [
        'openid',
        'token',
        'subscribe',
        'headimgurl',
        'subscribe_time',
        'sex',
        'unionid',
        'language',
        'city',
        'province',
        'nickname',
//        'username',
//        'password',
        'updated_at'
    ];

//    public function setPasswordAttribute($value)
//    {
//        $this->attributes['password'] = bcrypt($this->attributes['openid']);
//    }

    public function getSubscribeTimeAttribute($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

//    public function findForPassport($identifier) {
//        return $this->Where('username', $identifier)->first();
//    }

    public function belongsToReader()
    {
//        return $this->belongsTo(User::class);
        return $this->belongsTo(Reader::class, 'openid', 'openid');
    }


}
