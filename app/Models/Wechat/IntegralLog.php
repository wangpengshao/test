<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class IntegralLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'admin_wechat_integral_log';

    protected $fillable = ['token', 'rdid', 'description', 'number', 'type', 'operation'];


}
