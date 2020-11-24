<?php

namespace App\Models\Wechat;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;

class Admincontent extends Model
{

    protected $table = 'admin_wechat_admin_content';

    protected $fillable = ['type', 'openid', 'token', 'content', 'mediaId', 'user_id','image'];

    /**
     * 获取与用户关联的电话号码记录。
     */
    public function hasOneAdministrators()
    {
        return $this->hasOne( Administrator::class,'id','user_id');
    }
}
