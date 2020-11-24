<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSuggestionsTTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_suggestions_t', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title', 40)->nullable()->default('')->comment('标题');
			$table->boolean('is_bind', 1)->nullable()->default(0)->comment('绑定');
			$table->boolean('status', 1)->default(0)->comment('状态');
			$table->string('gather', 20)->nullable()->default('')->comment('收集');
			$table->string('token', 100)->nullable();
			$table->string('addgather', 200)->nullable()->comment('新增收集信息');
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
		Schema::drop('w_suggestions_t');
	}

}
