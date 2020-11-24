<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWechatIntegralExchangePrizeListTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wechat_integral_exchange_prize_list', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('rdid', 30)->nullable()->default('');
			$table->text('text', 65535)->nullable()->comment('说明');
			$table->string('openid', 80)->nullable()->default('');
			$table->string('code', 100)->nullable()->default('')->comment('兑奖码');
			$table->boolean('status')->comment('状态');
			$table->string('token', 30)->nullable()->default('');
			$table->integer('gather_id')->nullable()->default(0)->comment('信息id');
			$table->integer('prize_id')->nullable()->default(0)->comment('奖品id');
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
		Schema::drop('wechat_integral_exchange_prize_list');
	}

}
