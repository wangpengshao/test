<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSafeguardLikeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_safeguard_like', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 20)->nullable();
			$table->string('openid', 50)->nullable();
			$table->text('like', 65535)->nullable();
			$table->timestamps();
			$table->index(['token','openid'], 'openid_token');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_safeguard_like');
	}

}
