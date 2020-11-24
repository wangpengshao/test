<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWTplmsgFailureThirdTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_tplmsg_failure_third', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('t_id')->nullable()->comment('父id');
			$table->string('openid', 80)->nullable();
			$table->string('mes', 250)->nullable()->comment('失败原因');
			$table->dateTime('created_at')->nullable()->comment('时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_tplmsg_failure_third');
	}

}
