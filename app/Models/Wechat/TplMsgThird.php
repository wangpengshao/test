<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class TplMsgThird extends Model
{
    protected $table = 'w_tplmsg_third';

    protected $fillable = [
		'old_id',
        'token',
        'appid',
        'template_id',
        'title',
        'te1_da',
        'tpl_content',
        'redirect_type',
        'redirect_url',
        'mini_appid',
        'mini_path',
        'send_type',
        'group_tag',
        'openids',
        'status',
        'sended_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'reality_n',
        'failure_n',
    ];

    protected $casts = [
        'te1_da' => 'json',
    ];

    public function getGroupTagAttribute($value)
    {
        return explode(',', $value);
    }

}
