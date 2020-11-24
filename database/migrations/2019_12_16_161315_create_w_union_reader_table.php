<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWUnionReaderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_union_reader', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 20)->nullable();
			$table->string('openid', 50)->nullable();
			$table->string('rdid', 50)->nullable();
			$table->string('password', 500)->nullable();
			$table->timestamps();
			$table->boolean('is_bind')->default(0);
			$table->string('name', 60)->nullable();
			$table->string('origin_libcode', 15)->nullable()->comment('来源分馆代码');
			$table->string('origin_glc', 15)->nullable()->comment('来源全局馆ID');
			$table->boolean('is_cluster')->default(0)->comment('是否集群');
			$table->index(['token','openid'], 'openid_token');
			$table->index(['rdid','token'], 'rdid_token');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_union_reader');
	}

}
