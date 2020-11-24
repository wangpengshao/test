<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWInfowallUserinfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_infowall_userinfo', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('token', 60)->index('token');
			$table->integer('l_id')->comment('活动关联id');
			$table->string('username', 50)->nullable();
			$table->string('nickname', 60);
			$table->string('openid', 50);
			$table->string('headimgurl')->nullable()->comment('微信头像');
			$table->string('phone', 20)->nullable()->comment('用户手机号码');
			$table->boolean('status')->nullable()->default(1)->comment('管理状态(1 正常  2 被拉黑)');
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
		Schema::drop('w_infowall_userinfo');
	}

}
