<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatAttrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_attr', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 30);
            $table->string('name', 30)->comment('属性名称');
            $table->string('color', 20)->commen('标识颜色');
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
        Schema::dropIfExists('seat_attr');
    }
}
