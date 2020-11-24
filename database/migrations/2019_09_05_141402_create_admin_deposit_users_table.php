<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminDepositUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_deposit_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token')->index('token')->comment('读者名称');
			$table->string('rdid', 50)->index('rdid')->comment('读者证');
			$table->string('name');
			$table->string('idCard', 18)->comment('身份证');
			$table->decimal('deposit', 10)->unsigned()->comment('押金');
			$table->boolean('loss_sum')->default(0)->comment('违约次数');
			$table->boolean('status')->nullable()->default(0)->comment('0：未退；1：已退款；');
			$table->integer('create_time')->unsigned();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_deposit_users');
	}

}
