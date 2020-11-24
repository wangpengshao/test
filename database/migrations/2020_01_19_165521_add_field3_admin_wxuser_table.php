<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddField3AdminWxuserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_wxuser', function (Blueprint $table) {
            $table->tinyInteger('payment_opt')->default(0)->comment('0:公众号支付；1：工行聚合支付');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_wxuser', function (Blueprint $table) {
            //
        });
    }
}
