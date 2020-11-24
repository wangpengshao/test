<?php

namespace App\Models\Deposit;

use Illuminate\Database\Eloquent\Model;

class DepositUser extends Model
{
    protected $table = 'admin_deposit_users';
    public function getUpdatedAtColumn() {
        return null;
    }
}