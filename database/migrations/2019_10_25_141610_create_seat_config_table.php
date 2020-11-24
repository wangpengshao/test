<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', '30');
            $table->unsignedTinyInteger('status')->default(1)->comment('开放状态，1：开启；0：关闭');
            $table->unsignedTinyInteger('booking_switch')->default(1)->comment('预约开放状态，1：开启；0：关闭');
            $table->unsignedSmallInteger('keeptime')->default(0)->comment('座位保留时间');
            $table->unsignedTinyInteger('num')->default(1)->comment('单人可预约座位数');
            $table->unsignedTinyInteger('shortest_t')->default(0)->comment('最短预约时间');
            $table->unsignedSmallInteger('longest_t')->default(0)->comment('最长预约时间');
            $table->unsignedTinyInteger('ok_t')->default(0)->comment('可提前签到时间');
            $table->unsignedTinyInteger('delay_t')->default(0)->comment('可延迟签到时间');
            $table->unsignedTinyInteger('day_t')->default(0)->comment('可提前预约天数');
            $table->unsignedTinyInteger('violate_num')->default(0)->comment('最大违规次数');
            $table->unsignedTinyInteger('disabled_date')->default(0)->comment('禁用天数');
            $table->double('lat',10,6)->default('23.167297')->comment('纬度');
            $table->double('lng',10,6)->default('113.433570')->comment('经度');
            $table->double('purview',5,2)->default(0)->comment('签到范围');
            $table->text('notice')->nullable()->comment('公告信息');
            $table->timestamps();

            $table->unique('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_config');
    }
}
