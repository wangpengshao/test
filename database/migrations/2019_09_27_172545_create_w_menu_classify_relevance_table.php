<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWMenuClassifyRelevanceTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_menu_classify_relevance', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->integer('class_id');
            $table->integer('menu_id');
            $table->index(['class_id', 'menu_id'], 'class_menu_index');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('w_menu_classify_relevance');
    }

}
