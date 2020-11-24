<?php

namespace App\Models\Deposit;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'admin_deposit';
    public function getUpdatedAtColumn() {
        return null;
    }
}
