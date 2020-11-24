<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatCurrBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_curr_booking', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 30);
            $table->string('openid', '50')->nullable()->coment('用户openid');
            $table->string('rdid', '50')->comment('读者证号');
            $table->unsignedInteger('chart_id')->comment('座位id');
            $table->timestamp('s_time')->comment('开始时间');
            $table->timestamp('e_time')->nullable()->comment('结束时间');
            $table->unsignedTinyInteger('status')->default('0')->comment('0:待签到，1：已签到；2：已取消');
            $table->string('from', 30)->default('WeChat')->comment('预约渠道');
            $table->timestamp('sign_min')->nullable()->comment('签到开始时间');
            $table->timestamp('sign_max')->nullable()->comment('签到截止时间');
            $table->timestamp('sign_in')->nullable()->comment('签到时间、取消时间');
            $table->unsignedTinyInteger('is_sendMsg')->default('0')->comment('是否发送签到提示信息');
            $table->string('mark', '100')->nullable()->comment('备注');
            $table->timestamps();

            $table->index('token');
            $table->index(['rdid', 'openid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_curr_booking');
    }
}
