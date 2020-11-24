<?php

namespace App\Models\Wechat;

use App\Models\Wxuser;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'admin_wxuser_address';

    public function wxuser()
    {
        return $this->belongsTo(Wxuser::class);
    }
}
