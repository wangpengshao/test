<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class ActContent extends Model
{
    protected $table = 'admin_wechat_act_content';

    protected $fillable = ['token', 'rdid','status','openid','content','act_id'];

    public function fansInfo()
    {
        return $this->hasOne(Fans::class, 'openid','openid');
    }

}
