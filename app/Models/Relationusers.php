<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relationusers extends Model
{
    protected $table = 'admin_relation_users';
    protected $fillable=['parent_id','user_id','gene'];

}
