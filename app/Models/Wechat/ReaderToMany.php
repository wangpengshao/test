<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class ReaderToMany extends Model
{
    protected $table = 'admin_wechat_reader_relations';

    protected $fillable = ['token', 'rdid', 'act_collect', 'integral'];

    public function scopeFindReader($query, $reader = [])
    {
        $where = ['token' => $reader['token'], 'rdid' => $reader['rdid']];
        return $query->where($where);
    }

    public function getActCollectAttribute($value)
    {
        return (!empty($value)) ? explode(',', $value) : [];
    }

    public function setActCollectAttribute($options)
    {
        if (is_array($options)) {
            $this->attributes['act_collect'] = implode(',', $options);
        }
    }


}
