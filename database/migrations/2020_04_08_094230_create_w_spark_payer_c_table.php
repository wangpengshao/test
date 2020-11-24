<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSparkPayerCTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_spark_payer_c', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 20)->default('')->comment('单位名称');
			$table->string('pay_token', 30)->default('')->index('token');
			$table->string('secret', 60)->default('')->comment('密钥');
			$table->boolean('status')->default(0);
			$table->boolean('type')->default(0)->comment('类型');
			$table->decimal('money', 10)->default(0.00)->comment('余额');
			$table->timestamps();
			$table->string('ips', 200)->nullable()->default('')->comment('ip白名单');
			$table->dateTime('expiration_at')->nullable()->comment('过期时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_spark_payer_c');
	}

}
