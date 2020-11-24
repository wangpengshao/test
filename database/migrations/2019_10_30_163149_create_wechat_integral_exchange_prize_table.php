<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWechatIntegralExchangePrizeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('wechat_integral_exchange_prize', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title', 20)->nullable()->default('');
			$table->string('display')->nullable();
			$table->boolean('type')->default(0)->comment('次数类型(\'0\' => \'不限制\', \'1\' => \'按月数算\', \'2\' => \'按周算\', \'3\' => \'按天算\')');
			$table->integer('prize_type')->nullable()->comment('奖品类型');
			$table->integer('all_number')->default(0)->comment('可抽总次数');
			$table->integer('number')->default(0);
			$table->string('image', 250)->nullable()->default('');
			$table->string('token', 20)->nullable()->default('');
			$table->integer('inventory')->unsigned()->nullable()->default(0)->comment('库存');
			$table->integer('integral')->nullable()->default(0)->comment('积分');
			$table->string('qq', 20)->nullable();
			$table->string('phone', 20)->nullable();
			$table->decimal('money', 5)->nullable()->default(0.00)->comment('红包');
			$table->integer('reward_way')->nullable()->default(0)->comment('兑奖方式(0：线下 ; 1: 线上)');
			$table->dateTime('updated_at')->nullable();
			$table->integer('status')->nullable()->default(1)->comment('状态');
			$table->dateTime('end_at')->default('0000-00-00 00:00:00')->comment('兑奖结束时间');
			$table->dateTime('start_at')->default('0000-00-00 00:00:00')->comment('兑奖开始时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('wechat_integral_exchange_prize');
	}

}
