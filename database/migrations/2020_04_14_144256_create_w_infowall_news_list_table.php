<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWInfowallNewsListTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_infowall_news_list', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->nullable()->comment('关联用户表id');
			$table->string('token', 100)->nullable()->default('');
			$table->string('topic', 100)->nullable()->comment('话题');
			$table->string('content', 100)->nullable()->default('')->comment('心愿内容');
			$table->boolean('site')->nullable()->default(1)->comment('发弹幕的类型(1 场内  2 场外)');
			$table->integer('l_id')->nullable()->default(0)->comment('活动id');
			$table->boolean('status')->default(0)->comment('审核状态  0 未审核   1  通过   2不通过   ');
			$table->boolean('is_shelf')->nullable()->default(1)->comment('下架状态(1 没下架 2 已下架)');
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
		Schema::drop('w_infowall_news_list');
	}

}
