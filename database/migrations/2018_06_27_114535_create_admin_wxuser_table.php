<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminWxuserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_wxuser', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('wxname');
            $table->string('wxid');
            $table->char('headerpic');
            $table->string('token');
            $table->string('province');
            $table->string('city');
            $table->integer('fans_num');
            $table->string('phone');
            $table->string('email');
            $table->tinyInteger('winxintype');
            $table->string('appid');
            $table->string('appsecret');
            $table->string('opacurl');
            $table->char('qr_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_wxuser');
    }
}
