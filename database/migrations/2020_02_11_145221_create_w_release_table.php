<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWReleaseTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_release', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('type')->default(0)->comment('类型');
			$table->boolean('template_id')->default(0)->comment('模版id');
			$table->boolean('target_type')->default(0)->comment('目标类型:0全部,1部分');
			$table->string('target_token', 1000)->default('')->comment('目标');
			$table->string('content', 100)->nullable()->default('')->comment('内容');
			$table->string('url', 200)->nullable()->default('')->comment('链接');
			$table->string('img', 200)->nullable()->default('')->comment('图片');
			$table->integer('order')->default(0)->comment('排序');
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
		Schema::drop('w_release');
	}

}
