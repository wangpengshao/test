<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWMenuClassifyTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_menu_classify', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->integer('order')->default(0);
            $table->string('title', 20)->default('')->comment('类名');
            $table->string('desc', 30)->nullable()->default('')->comment('描述');
            $table->boolean('is_show')->default(0);
            $table->string('token', 20)->default('')->index('token_index');
            $table->string('logo', 250)->nullable()->comment('logo');
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
        Schema::drop('w_menu_classify');
    }

}
