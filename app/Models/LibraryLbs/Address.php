<?php

namespace App\Models\LibraryLbs;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'admin_wxuser_address';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
