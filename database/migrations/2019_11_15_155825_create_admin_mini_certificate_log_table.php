<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminMiniCertificateLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_mini_certificate_log', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 20)->nullable()->default('')->index('token_index');
			$table->string('mini_token', 30)->nullable()->default('')->index('mini_token_index');
			$table->string('rdid', 30)->nullable()->default('');
			$table->string('openid', 80)->nullable()->default('');
			$table->boolean('status')->default(0)->comment('0,1,2');
			$table->boolean('type')->default(1)->comment('0普通1实名');
			$table->string('rdname', 20)->nullable()->default('');
			$table->string('rdpasswd', 500)->nullable()->default('');
			$table->string('rdcertify', 30)->nullable()->default('');
			$table->string('rdlib', 20)->nullable()->default('');
			$table->string('operator', 20)->nullable()->default('');
			$table->string('rdtype', 20)->nullable()->default('');
			$table->text('data', 65535)->nullable()->comment('其他');
			$table->boolean('is_pay')->default(0)->comment('在线支付');
			$table->string('prepay_id', 80)->nullable()->default('');
			$table->string('order_id', 80)->nullable()->default('')->comment('商户单号');
			$table->timestamps();
			$table->text('imgData', 65535)->nullable()->comment('图片');
			$table->boolean('check_s')->default(0)->comment('0免1已-1未');
			$table->dateTime('check_at')->nullable();
			$table->string('check_info', 100)->nullable()->comment('审核原因');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_mini_certificate_log');
	}

}
