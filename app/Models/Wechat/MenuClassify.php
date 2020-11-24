<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MenuClassify extends Model
{
    protected $table = 'w_menu_classify';

//    protected $hidden = ['pivot'];

    public function menus()
    {
        return $this->belongsToMany(IndexMenu::class, 'w_menu_classify_relevance', 'class_id', 'menu_id')
            ->orderBy('order', 'DESC')
            ->select('caption', 'id', 'order', 'icon', 'flag', 'flagColor');
    }

    public function getLogoAttribute($logo)
    {
        if ($logo) {
            return Storage::disk(config('admin.upload.disk'))->url($logo);
        }
        return '';
    }

    public function scopeIsShow($query, $token)
    {
        $query->where('token', $token)->where('is_show', 1)->orderBy('order', 'desc');
    }

}
