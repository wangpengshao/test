<?php

namespace App\Models\Suggestions;

use App\Models\Wechat\Fans;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SuggestionsList extends Model
{

    protected $table = 'w_suggestions_l';

    protected $casts = [
        'img' => 'json',
        'other' => 'json'
    ];

    public function hasOneSuggestionType()
    {
        return $this->hasOne(SuggestionsTypes::class, 'id', 's_id');
    }

    public function hasManyMessages()
    {
        return $this->hasMany(SuggestionsMessages::class, 'm_id', 'id');
    }

    public function fans()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid');
    }


    public function getImgAttribute($img)
    {
        $data = [];
        if ($img) {
            $imgs = json_decode($img, true);
            if ($imgs) {
                foreach ($imgs as $k => $v) {
                    $imgs[$k] = Storage::disk(config('admin.upload.disk'))->url($v);
                }
                $data = $imgs;
            }
        }
        return $data;
    }

}
