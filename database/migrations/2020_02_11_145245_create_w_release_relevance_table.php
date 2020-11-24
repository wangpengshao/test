<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWReleaseRelevanceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_release_relevance', function(Blueprint $table)
		{
			$table->integer('r_id');
			$table->integer('data_id')->comment('数据id');
			$table->string('token', 30)->nullable()->default('');
			$table->index(['r_id','data_id'], 'class_menu_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_release_relevance');
	}

}
