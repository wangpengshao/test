<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSparkPayerLTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_spark_payer_l', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('pay_token', 20)->nullable()->default('')->index('token_index');
			$table->integer('amount')->default(0)->comment('金额');
			$table->boolean('type')->default(0)->comment('类型');
			$table->string('openid', 80)->nullable()->default('');
			$table->string('return_code', 16)->nullable()->default('')->comment('通信标识');
			$table->string('result_code', 16)->nullable()->default('')->comment('业务标识');
			$table->string('payment_no', 70)->nullable()->default('')->comment('微信付款单号');
			$table->string('partner_trade_no', 40)->nullable()->default('')->comment('商户单号');
			$table->string('desc', 110)->nullable()->default('')->comment('备注');
			$table->text('response_info')->nullable()->comment('响应数据');
			$table->decimal('current_money', 10)->default(0.00)->comment('当前余额');
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
		Schema::drop('w_spark_payer_l');
	}

}
