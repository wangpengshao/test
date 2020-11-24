<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminWxuserOtherTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_wxuser_other', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('wxuser_id')->index('wxuser_id_index');
			$table->string('mn_resources_appid', 20)->nullable()->default('')->comment('电子资源');
			$table->string('mn_resources_key', 20)->nullable()->default('')->comment('电子资源');
			$table->boolean('mn_resources_sw')->nullable()->default(0)->comment('开关');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_wxuser_other');
	}

}
