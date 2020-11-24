<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWExpireNoticeRecordTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_expire_notice_record', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('token', 40)->default('');
			$table->string('rdid', 60)->nullable()->default('')->comment('读者证号');
			$table->string('rdloginid', 60)->nullable()->default('')->comment('手机号码');
			$table->string('rdname', 60)->nullable()->default('')->comment('姓名');
			$table->string('rdemail', 60)->nullable()->default('')->comment('邮箱');
			$table->string('rdcertify', 20)->nullable()->default('')->comment('身份');
			$table->boolean('status')->default(0)->comment('发送状态0失败1成功');
			$table->boolean('is_bind')->default(0)->comment('绑定状态0未绑1已绑');
			$table->string('openid', 60)->nullable()->comment('接收者openid');
			$table->integer('t_id')->default(0)->comment('任务id');
			$table->text('info')->nullable()->comment('借阅信息');
			$table->dateTime('created_at')->nullable()->comment('创建时间');
			$table->dateTime('send_at')->nullable()->comment('发送时间');
			$table->index(['token','openid'], 'token_index_openid');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_expire_notice_record');
	}

}
