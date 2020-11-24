<?php

namespace App\Models\Deposit;

use Illuminate\Database\Eloquent\Model;

class DepositEveryday extends Model
{
    protected $table = 'admin_deposit_everyday';
    public function getUpdatedAtColumn() {
        return null;
    }
}