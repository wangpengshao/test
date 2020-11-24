<?php

namespace App\Models\Deposit;

use Illuminate\Database\Eloquent\Model;

class DepositLog extends Model
{
    protected $table = 'admin_deposit_log';
    public function getUpdatedAtColumn() {
        return null;
    }
}
