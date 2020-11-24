<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWInfowallActConfigTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_infowall_act_config', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title', 40)->nullable()->default('')->comment('标题');
			$table->string('token', 20)->nullable()->default('')->index('token_index');
			$table->string('slogan', 20)->nullable()->default('')->comment('标语');
			$table->text('rule', 65535)->nullable()->comment('活动规则');
			$table->string('describe', 100)->nullable()->comment('引导⽂案⽂本');
			$table->boolean('type')->nullable()->default(0)->comment('可发弹幕类型');
			$table->boolean('is_bind', 1)->nullable()->default(0)->comment('绑定');
			$table->boolean('is_subscribe', 1)->default(0)->comment('关注');
			$table->boolean('is_check')->default(0)->comment('是否需要人工审核（0 不需要  1 需要）');
			$table->boolean('is_share')->nullable()->default(0)->comment('弹幕共享状态( 0 不共享   1共享)');
			$table->boolean('is_custom')->nullable()->default(1)->comment('是否可以自定义文本( 0 否  1 可以)');
			$table->boolean('status', 1)->default(0)->comment('活动开关');
			$table->string('gather', 100)->nullable()->default('')->comment('收集');
			$table->string('addgather', 200)->nullable()->comment('新增收集信息');
			$table->string('image', 250)->nullable()->default('');
			$table->integer('number')->nullable()->comment('弹幕数量');
			$table->boolean('show_way')->nullable()->default(1)->comment('展示方式');
			$table->dateTime('start_at')->nullable()->comment('活动开始时间');
			$table->dateTime('end_at')->nullable()->comment('活动结束时间');
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
		Schema::drop('w_infowall_act_config');
	}

}
