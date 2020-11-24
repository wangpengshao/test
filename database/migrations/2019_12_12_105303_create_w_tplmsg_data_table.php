<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWTplmsgDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_tplmsg_data', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 50)->nullable()->index('token');
			$table->string('template_id', 50)->nullable();
			$table->string('title')->nullable();
			$table->text('te1_da')->comment('发送的数据');
			$table->string('tpl_content')->nullable();
			$table->boolean('redirect_type')->nullable()->default(0)->comment('0：不跳转；1：网页；2：小程序');
			$table->string('redirect_url')->nullable()->comment('跳转网址');
			$table->string('mini_appid', 30)->nullable()->comment('小程序appid');
			$table->string('mini_path')->nullable()->comment('小程序path');
			$table->boolean('send_type')->nullable()->comment('群发方式，0：全部粉丝；1：分组；2：绑定用户；3：指定粉丝');
			$table->string('group_tag')->nullable()->comment('分组标签');
			$table->string('openids', 500)->nullable()->comment('指定粉丝');
			$table->boolean('status')->nullable()->default(0)->comment('发送状态');
			$table->dateTime('sended_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_tplmsg_data');
	}

}
