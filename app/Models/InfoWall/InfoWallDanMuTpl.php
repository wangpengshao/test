<?php

namespace App\Models\InfoWall;

use Illuminate\Database\Eloquent\Model;

class InfoWallDanMuTpl extends Model
{
    protected $table = 'w_infowall_danmu_tpl';

    protected $fillable = [
        'token', 'parent_id', 'p_name', 'type', 'l_id', 's_name'
    ];

    public function scopeCurrent($query, $token, $id)
    {
        return $query->where([
            'id' => $id,
            'token' => $token,
        ]);
    }
    // 关联当前表已经有添加过共享模板中的数据

    public function parent()
    {
        return $this->hasOne(get_class($this), "s_id", 'id')->where('token', session('wxtoken'));
    }

}
