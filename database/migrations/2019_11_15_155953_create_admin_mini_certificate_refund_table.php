<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminMiniCertificateRefundTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_mini_certificate_refund', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 20)->nullable()->default('')->index('token_index');
			$table->string('mini_token', 30)->nullable()->index('mini_token_index');
			$table->boolean('status')->default(0)->comment('-1败0未1成');
			$table->text('data', 65535)->nullable()->comment('内容');
			$table->string('order_id', 80)->nullable()->default('')->index('orderId_index')->comment('商号');
			$table->string('out_refund_no', 80)->nullable()->default('')->comment('退号');
			$table->string('refund_id', 80)->nullable()->default('')->comment('微号');
			$table->decimal('refund_fee', 5)->nullable()->comment('退额');
			$table->decimal('total_fee', 5)->nullable()->comment('总');
			$table->timestamps();
			$table->string('refund_str', 120)->nullable()->default('')->comment('备注');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_mini_certificate_refund');
	}

}
