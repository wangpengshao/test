<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_queue', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 30);
            $table->string('openid', '50')->coment('排队者openid');
            $table->string('rdid', '50')->comment('排队者读者证号');
            $table->unsignedInteger('chart_id')->comment('座位id');
            $table->unsignedTinyInteger('status')->default('1')->comment('1:排队中');
            $table->timestamp('seating_time')->comment('排队入座时间');
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
        Schema::dropIfExists('seat_queue');
    }
}
