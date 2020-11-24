<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    protected $table = 'w_release';

    public function getTargetTokenAttribute($options)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        return $options;
    }

    public function setTargetTokenAttribute($options)
    {
        if (is_array($options)) {
            $options = join(',', $options);
        }
        $this->attributes['target_token'] = $options;
    }
}
