<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class TransactType extends Model
{
    protected $table = 'admin_wechat_certificate_type';

    protected $casts = [
        'password_limit' => 'json',
    ];

}
