<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminWxuserAggregatePaymentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_wxuser_aggregate_payment', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('wxuser_id')->index('wxuser_id');
			$table->string('icbc_app_id', 50)->nullable()->comment('APP的编号');
			$table->string('icbc_mer_id', 30)->nullable()->comment('商户号');
			$table->string('icbc_sign_type', 100)->nullable()->default('RSA2')->comment('签名类型');
			$table->string('icbc_private_key')->nullable()->comment('签名私钥');
			$table->string('icbc_public_key')->nullable()->comment('签名公钥');
			$table->string('icbc_encrypt_type', 10)->nullable()->default('AES')->comment('加密类型');
			$table->string('icbc_encrypt_key')->nullable()->comment('加密key');
			$table->string('icbc_geteway_publickey')->nullable()->comment('网关公钥');
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
		Schema::drop('admin_wxuser_aggregate_payment');
	}

}
