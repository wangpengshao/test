<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatByScanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_by_scan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 30);
            $table->string('openid', '50')->coment('用户openid');
            $table->string('rdid', '50')->comment('读者证号');
            $table->unsignedInteger('chart_id')->comment('座位id');
            $table->timestamp('s_time')->comment('入座时间');
            $table->timestamp('e_time')->nullable()->comment('离座时间');
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
        Schema::dropIfExists('seat_by_scan');
    }
}
