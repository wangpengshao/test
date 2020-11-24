<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWechatIntegralHonoreeGatherTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wechat_integral_honoree_gather', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('openid', 80)->nullable()->default('');
			$table->string('token', 30)->nullable()->default('')->index('token_index_lid');
			$table->string('phone', 20)->nullable()->default('')->comment('手机');
			$table->string('address')->nullable()->default('')->comment('身份证');
			$table->string('name', 20)->nullable()->default('')->comment('姓名');
			$table->dateTime('created_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wechat_integral_honoree_gather');
	}

}
