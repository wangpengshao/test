<?php
namespace App\Models\Mini;
use Illuminate\Database\Eloquent\Model;
class StoreUserInfo extends Model
{
    public $timestamps=false;
    protected $table = 'mini_e_resources_f';
    protected $fillable = ['token', 'openid'];
}