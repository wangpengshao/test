<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;

class RelationWxuser extends Model
{
    protected $table = 'admin_relation_wxuser';
    protected $fillable = ['wxuser_id', 'user_id'];

    public function users()
    {
        return $this->belongsTo(Administrator::class, 'id', 'user_id');
    }
}
