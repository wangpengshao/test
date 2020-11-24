<?php

namespace App\Models\Wechat;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;

class TcContent extends Model
{
    protected $table = 'admin_wechat_tc_content';

    protected $fillable = ['type', 'user_id','content','views','title','description','status'];

//    public function hasOneAdministrator()
//    {
//        return $this->hasOne(Administrator::class,'id','user_id');
//    }
}
