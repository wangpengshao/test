<?php

namespace App\Models\Recommend;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RecommendIsbn extends Model
{

    protected $table = 'w_recommend_isbn';

    public $fillable = [
        'isbn', 'token', 's_id', 'intro', 'c_id', 'reason', 'view_num', 'col_num'
    ];

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getImageAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    public function getLogoAttribute($logo)
    {
        if ($logo) {
            return Storage::disk(config('admin.upload.disk'))->url($logo);
        }
        return '';
    }

    public function scopeCurrent($query, $token, $id)
    {
        return $query->where([
            'token' => $token,
            'id' => $id
        ]);
    }

    public function hasOneTitle()
    {
        return $this->hasOne(RecommendBooks::class, 'id', 's_id');
    }


}
