<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TplMsgData extends Model
{
    use SoftDeletes;

    protected $table = 'w_tplmsg_data';

    protected $fillable = [
        'token',
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

    public function setGroupTagAttribute($value)
    {
        $this->attributes['group_tag'] = implode(',', $value);
    }

    public function setOpenidsAttribute($value)
    {
        $this->attributes['openids'] = preg_replace("/\\r\\n/", ',', $value);
    }

    public function getOpenidsAttribute($value)
    {
        return preg_replace("/,/", "\r\n", $value);;
    }
}
