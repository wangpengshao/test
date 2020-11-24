<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatChartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_chart', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 30);
            $table->unsignedInteger('region_id')->comment('区域id');
            $table->unsignedSmallInteger('numid')->comment('座位编号id');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态，1：空位；2：使用中；3：关闭；');
            $table->unsignedTinyInteger('seating_type')->default(0)->comment('入座类型，0:扫码入座，1：预约入座');
            $table->unsignedInteger('seated_id')->nullable()->comment('入座记录id');
            $table->unsignedInteger('queue_id')->nulllable()->comment('排队id');
            $table->string('rdid', '50')->nullable()->comment('读者证号');
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
        Schema::dropIfExists('seat_chart');
    }
}
