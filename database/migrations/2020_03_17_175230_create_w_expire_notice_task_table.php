<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWExpireNoticeTaskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_expire_notice_task', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('token', 40)->default('');
			$table->integer('last_page')->nullable()->default(0)->comment('记录接口页数');
			$table->integer('last_id')->nullable()->default(0)->comment('记录发送id');
			$table->boolean('is_migrate')->nullable()->default(0)->comment('迁移');
			$table->boolean('is_retry')->nullable()->default(0)->comment('是否是重试');
			$table->boolean('status')->default(0)->comment('-1异常,0创建,1采集中,2发布中,3执行中,4执行完成');
			$table->integer('valid_n')->nullable()->default(0)->comment('有效数量');
			$table->integer('success_n')->nullable()->default(0)->comment('成功数量');
			$table->integer('total_n')->nullable()->default(0)->comment('总数');
			$table->text('conf_data')->nullable()->comment('配置项纪录');
			$table->string('exception_info', 200)->nullable()->comment('异常说明');
			$table->dateTime('retry_at')->nullable()->comment('重试时间');
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
		Schema::drop('w_expire_notice_task');
	}

}
