<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWRecommendMTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_recommend_m', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('m_id')->comment('关联书单id');
			$table->boolean('r_id')->nullable()->comment('分辨是读者或管理员回复的信息--标记（1 代表读者; 2 代表管理员）');
			$table->string('r_reply', 200)->nullable()->default('')->comment('读者回复信息');
			$table->string('a_reply', 200)->nullable()->default('')->comment('管理员回复信息');
			$table->string('token', 100)->nullable()->default('');
			$table->string('openid', 50)->nullable();
			$table->string('rdid', 50)->nullable();
			$table->string('name', 50)->nullable()->comment('留言人姓名');
			$table->boolean('is_reading')->default(0)->comment('读者回复信息是否已读状态');
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
		Schema::drop('w_recommend_m');
	}

}
