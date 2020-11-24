<?php

namespace App\Models\Mini;

use Illuminate\Database\Eloquent\Model;

class RegistrationType extends Model
{
    const UPDATED_AT = null;

    protected $table = 'mini_registration_t';

    protected $casts = [
        'password_limit' => 'json',
    ];

    public function getDataAttribute($data)
    {
        return (!empty($data)) ? explode(',', $data) : [];
    }

    public function setDataAttribute($data)
    {
        if (is_array($data)) {
            $this->attributes['data'] = implode(',', $data);
        }
    }

    public function getRegionAttribute($data)
    {
        return (!empty($data)) ? explode(',', $data) : [];
    }

    public function setRegionAttribute($data)
    {
        if (is_array($data)) {
            $this->attributes['region'] = implode(',', $data);
        }
    }

    public function getPhoneRegionAttribute($data)
    {
        return (!empty($data)) ? explode(',', $data) : [];
    }

    public function setPhoneRegionAttribute($data)
    {
        if (is_array($data)) {
            $this->attributes['phone_region'] = implode(',', $data);
        }
    }

    public function getImgDataAttribute($data)
    {
        return (!empty($data)) ? explode(',', $data) : [];
    }

    public function setImgDataAttribute($data)
    {
        if (is_array($data)) {
            $this->attributes['img_data'] = implode(',', $data);
        }
    }

}
