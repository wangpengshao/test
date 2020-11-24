<?php

namespace App\Models\Mini;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RegistrationImg extends Model
{
    const UPDATED_AT = null;

    protected $table = 'mini_registration_i';

    public function getImgAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

}
