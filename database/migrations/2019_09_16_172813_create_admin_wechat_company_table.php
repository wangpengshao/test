<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminWechatCompanyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_wechat_company', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('amapid', 50)->default('');
			$table->boolean('display')->default(1);
			$table->string('token', 50)->default('')->index('token');
			$table->string('name', 100)->default('');
			$table->string('username', 60);
			$table->string('password', 32);
			$table->string('shortname', 50)->default('');
			$table->string('mp', 11)->default('');
			$table->string('tel', 20)->default('');
			$table->string('address', 200)->default('');
			$table->float('latitude', 10, 0);
			$table->float('longitude', 100, 0);
			$table->text('intro', 65535);
			$table->integer('catid')->default(0);
			$table->integer('taxis')->default(0);
			$table->boolean('isbranch')->default(0);
			$table->string('logourl', 180)->default('');
			$table->integer('area_id')->default(0);
			$table->integer('cate_id')->default(0);
			$table->integer('market_id')->default(0);
			$table->string('mark_url', 200)->default('');
			$table->char('add_time', 10)->default(0);
			$table->dateTime('updated_at')->nullable();
			$table->date('created_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_wechat_company');
	}

}
