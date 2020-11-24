<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class RelevanceMenu extends Model
{
    protected $table = 'wechat_menu_relevance';
    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $casts = [
        'extra' => 'json',
    ];

//    public function user()
//    {
////        return $this->belongsTo(User::class);
//    }


    public function getAddInfoAttribute($options)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        return $options;
    }

    public function setAddInfoAttribute($options)
    {
        if (is_array($options)) {
            $options = join(',', $options);
        }
        $this->attributes['add_info'] = $options;
    }

}
