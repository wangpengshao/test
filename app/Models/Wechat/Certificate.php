<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = 'admin_wechat_certificate';

    public function getOptionsAttribute($options)
    {
        return (!empty($options)) ? explode(',', $options) : [];
    }

    public function setOptionsAttribute($options)
    {
        if (is_array($options)) {
            $this->attributes['options'] = implode(',', $options);
        }
    }

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
            $this->attributes['imgData'] = implode(',', $data);
        }
    }

}
