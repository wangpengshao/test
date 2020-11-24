<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWLibNoticeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_lib_notice', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title', 20)->nullable()->default('')->comment('公告标题');
			$table->string('content', 250)->nullable()->default('')->comment('公告内容');
			$table->boolean('status')->nullable()->default(1)->comment('公告显示状态');
			$table->string('token', 20)->nullable()->default('');
			$table->string('file_path', 100)->nullable()->comment('附件路径');
			$table->dateTime('start_at')->nullable()->comment('开始显示的起始时间');
			$table->dateTime('end_at')->nullable()->comment('显示的结束时间');
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
		Schema::drop('w_lib_notice');
	}

}
