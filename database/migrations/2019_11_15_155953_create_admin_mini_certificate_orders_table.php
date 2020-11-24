<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminMiniCertificateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_mini_certificate_orders', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 20)->nullable()->default('');
			$table->string('mini_token', 30)->nullable()->default('')->index('mini_token_index');
			$table->string('transaction_id', 80)->nullable()->default('')->comment('微信单号');
			$table->string('rdid', 30)->nullable()->default('');
			$table->decimal('price', 5)->nullable()->default(0.00)->comment('实际金额');
			$table->decimal('origin_price', 5)->nullable()->default(0.00)->comment('原金额');
			$table->decimal('cash_fee', 5)->nullable()->default(0.00)->comment('现金支付');
			$table->string('openid', 80)->nullable()->default('');
			$table->boolean('pay_status')->default(0)->comment('-1败0未1已2退');
			$table->boolean('pay_type')->default(0)->comment('1扫码');
			$table->string('prepay_id', 80)->nullable()->default('');
			$table->string('order_id', 80)->nullable()->default('')->index('orderId_index')->comment('商户单号');
			$table->dateTime('pay_at')->nullable()->comment('支付');
			$table->timestamps();
			$table->index(['token','mini_token'], 'token_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_mini_certificate_orders');
	}

}
