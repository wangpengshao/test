<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 30);
            $table->string('rdid', '50')->comment('读者证号');
            $table->dateTime('last_date')->comment('最近登录时间');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态，1：正常 ，0：黑名单');
            $table->unsignedSmallInteger('violations')->default('0')->comment('违规次数');
            $table->dateTime('forbidden')->comment('禁止使用预约截止时间');
            $table->timestamps();

            $table->index(['token', 'rdid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_users');
    }
}
