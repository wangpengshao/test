<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminMiniPayconfigTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_mini_payconfig', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('mini_id')->unsigned()->index('mini_id');
			$table->string('app_id', 40)->nullable()->default('');
			$table->string('mch_id', 40)->nullable()->default('');
			$table->string('key', 50)->nullable()->default('');
			$table->string('key_path', 200)->nullable()->default('');
			$table->string('cert_path', 200)->nullable()->default('');
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
		Schema::drop('admin_mini_payconfig');
	}

}
