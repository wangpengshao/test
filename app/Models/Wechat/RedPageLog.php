<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class RedPageLog extends Model
{
    protected $table = 'w_redpage_log';

    const UPDATED_AT = null;

    protected $fillable = [
        'return_code', 'return_msg', 'result_code', 'err_code', 'err_code_des',
        'mch_billno', 'mch_id', 'wxappid', 're_openid', 'total_amount', 'send_listid'
    ];

}