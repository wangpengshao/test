<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibNotice extends Model
{
    protected $table = 'w_lib_notice';

    public $fillable = [
        'token', 'title', 'content', 'status', 'token', 'file_path', 'start_at', 'end_at'
    ];

}
