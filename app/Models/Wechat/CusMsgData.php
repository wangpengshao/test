<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CusMsgData extends Model
{
    use SoftDeletes;

    protected $table = 'w_custommsg_data';

    protected $casts = [
        'text_data' => 'json',
        'news_data' => 'json',
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
