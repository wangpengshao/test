<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatViolationsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_violations_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 30);
            $table->string('rdid', '50')->comment('读者证号');
            $table->unsignedTinyInteger('type')->default(1)->comment('违约类型,1:签到违约');
            $table->unsignedInteger('scan_id')->nullable()->comment('扫码入座id');
            $table->unsignedInteger('booking_id')->nullable()->comment('预约入座id');
            $table->string('mark', '100')->nullable()->comment('备注');
            $table->dateTime('created_at');

            $table->index('rdid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_violations_log');
    }
}
