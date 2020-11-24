<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
//use Watson\Rememberable\Rememberable;

class Image extends Model
{
//    use Rememberable;

    protected $table = 'demo_images';

    protected $fillable = ['uploader','caption','image'];


    public function author()
    {
        return $this->belongsTo(User::class, 'uploader');
    }


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
}
