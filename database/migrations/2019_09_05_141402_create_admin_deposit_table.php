<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminDepositTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_deposit', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 100)->index('token');
			$table->decimal('total_money', 10)->unsigned()->comment('总金额');
			$table->string('week1', 50);
			$table->string('week2', 50);
			$table->string('week3', 50);
			$table->string('week4', 50);
			$table->string('week5', 50);
			$table->string('week6', 50);
			$table->string('week0', 50);
			$table->boolean('block')->default(30)->comment('时段');
			$table->string('holiday', 200);
			$table->string('black_rule')->comment('黑名单制');
			$table->boolean('before_time')->comment('提前天数');
			$table->boolean('status')->nullable()->default(1)->comment('开放状态');
			$table->integer('create_time')->unsigned();
			$table->string('extend1', 50);
			$table->dateTime('updated_at')->nullable();
			$table->integer('deposit_grade')->nullable();
			$table->string('notice')->nullable()->comment('公告');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_deposit');
	}

}
