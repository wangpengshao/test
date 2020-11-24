<?php

namespace App\Models\Recommend;

use App\Models\Recommend\MessageList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RecommendBooks extends Model
{

    protected $table = 'w_recommend_sd';

    public $fillable = [
        'title', 'image', 'status', 'intro', 'token', 'stage_id', 'isbn', 'a_status', 'c_status'
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

    public function hasManyMessages()
    {
        return $this->hasMany(MessageList::class, 'm_id', 'id');
    }

    public function hasManyIsbn()
    {
        return $this->hasMany(RecommendIsbn::class, 's_id', 'id');
    }

    public function hasManyCol()
    {
        return $this->hasOne(RecommendIsbn::class, 'c_id', 'id')->where('token', session('wxtoken'));
    }

    public function hasOneAddIsbn()
    {
        return $this->hasOne(Isbn::class, 's_id', 'id');
    }

    public function getIsbnAttribute($extra)
    {
        return array_values(json_decode($extra, true) ?: []);
    }

    public function setIsbnAttribute($extra)
    {
        $this->attributes['isbn'] = json_encode(array_values($extra));
    }

}
