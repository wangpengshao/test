<?php

namespace App\Models\InfoWall;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InfoWallConfig extends Model
{

    protected $table = 'w_infowall_act_config';

    public function scopeCurrent($query, $token, $id)
    {
        return $query->where([
            'id' => $id,
            'token' => $token,
        ]);
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

    public function getGatherAttribute($options)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        return $options;
    }

    public function setGatherAttribute($options)
    {
        if (is_array($options)) {
            $options = join(',', $options);
        }
        $this->attributes['gather'] = $options;
    }

    public function getAddgatherAttribute($extra)
    {
        return array_values(json_decode($extra, true) ?: []);
    }

    public function setAddgatherAttribute($extra)
    {
        $this->attributes['addgather'] = json_encode(array_values($extra));
    }

    public function hasManyTpl()
    {
        return $this->hasMany(InfoWallDanMuTpl::class, 'l_id', 'id');
    }

}
