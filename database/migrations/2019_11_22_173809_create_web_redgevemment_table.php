<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWebRedgevemmentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('web_redgevemment', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 20)->nullable()->index('token');
			$table->string('name', 50);
			$table->string('logo')->nullable();
			$table->boolean('status')->default(1);
			$table->dateTime('date_start');
			$table->dateTime('date_end');
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
		Schema::drop('web_redgevemment');
	}

}
