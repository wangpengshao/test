<?php

namespace App\Models\LibraryLbs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class Company
 * @package App\Models\LibraryLbs
 */
class Company extends Model
{
    protected $table = 'admin_wechat_company';
    protected $fillable = ['is_show', 'p_id', 'token', 'name', 'telephone', 'phone', 'lat', 'lng', 'address', 'intro'];

    public function getLogoAttribute($logo)
    {
        if ($logo) {
            return Storage::disk(config('admin.upload.disk'))->url($logo);
        }
        return '';
    }

}
