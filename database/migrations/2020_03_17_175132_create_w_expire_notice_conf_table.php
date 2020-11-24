<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWExpireNoticeConfTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_expire_notice_conf', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('token', 40)->default('');
			$table->boolean('day_n')->nullable()->default(0)->comment('将过期天数');
			$table->string('libcode', 20)->nullable()->comment('分馆代码');
			$table->time('time_at')->nullable()->comment('通知的时间');
			$table->string('template_id', 50)->nullable()->comment('模版消息');
			$table->text('te1_da')->nullable()->comment('模版内容');
			$table->boolean('status')->nullable()->default(0)->comment('状态');
			$table->string('redirect_url', 191)->nullable()->comment('跳转链接');
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
		Schema::drop('w_expire_notice_conf');
	}

}
