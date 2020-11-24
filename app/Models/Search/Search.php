<?php

namespace App\Models\Search;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    protected $table = 'admin_wxuser';

    public function scopeParamSearch($query, $data)
    {
        if (!empty($data['wxname'])) {
            // 图书馆名称筛选
            $query = $query->where('wxname', 'like', "%{$data['wxname']}%");
        } else if (!empty($data['token'])) {
            // token筛选
            $query = $query->where('token', $data['token']);
        }
        return $query;
    }
}