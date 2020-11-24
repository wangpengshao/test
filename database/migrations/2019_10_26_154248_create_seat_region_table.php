<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatRegionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_region', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pid')->default('0')->comment('所属大区域');
            $table->string('token', 30);
            $table->unsignedSmallInteger('sort')->comment('排序');
            $table->string('name', 25)->comment('名称');
            $table->unsignedTinyInteger('status')->default('1')->comment('区域状态，1开放，0关闭');
            $table->unsignedTinyInteger('booking_switch')->default('1')->comment('预约开关，1开放，0关闭');
            $table->time('s_time')->comment('开放时间');
            $table->time('e_time')->comment('关闭时间');
            $table->unsignedSmallInteger('chart_nums')->comment('座位数');
            $table->unsignedTinyInteger('cols')->default('6')->comment('列数');
            $table->string('img', 300)->nullable()->comment('区域图纸');
            $table->unsignedTinyInteger('is_hot')->default('0')->comment('是否热门区');
            $table->timestamps();

            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_region');
    }
}
