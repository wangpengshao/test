<?php

namespace App\Models\CollectCard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CardConfig extends Model
{
    protected $table = 'w_collect_card_c';

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

    public function hasManyLog()
    {
        return $this->hasMany(CollectLog::class, 'c_id', 'id');
    }
}
