<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminDepositEverydayTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_deposit_everyday', function(Blueprint $table)
		{
			$table->integer('deposit_id')->index('deposit_id');
			$table->decimal('amount', 10)->unsigned()->comment('总数');
			$table->decimal('balance', 10)->unsigned()->comment('余额');
			$table->date('date')->index('date')->comment('日期');
			$table->integer('update_time')->unsigned()->comment('变更时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_deposit_everyday');
	}

}
