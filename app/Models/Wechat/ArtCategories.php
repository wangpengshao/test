<?php

namespace App\Models\Wechat;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class ArtCategories extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'admin_wechat_art_categories';

    public function scopeGetparent($query)
    {
        return $query->where([
            'token' => session('wxtoken'),
            'parent_id' => 0
        ])->orderBy('order')->pluck('title', 'id')->prepend('root', 0)->toArray();

    }

    public function scopeGetAll($query)
    {
        return $query->whereToken(session('wxtoken'))
            ->where('parent_id', '<>', '0')->orderBy('order')->pluck('title', 'id')->toArray();
    }

}
