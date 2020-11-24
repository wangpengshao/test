<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWInfowallDanmuTplTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_infowall_danmu_tpl', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('p_name', 120)->default('')->comment('一级类-话题');
			$table->string('s_name')->nullable()->comment('二级类-心愿');
			$table->string('token', 20)->nullable()->default('');
			$table->boolean('type')->default(2)->index('type')->comment('模板话题类型');
			$table->integer('l_id')->nullable()->comment('活动id');
			$table->integer('s_id')->nullable()->comment('从公共分享资源添加的分享id');
			$table->boolean('is_share')->nullable()->default(0)->comment('共享状态');
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
		Schema::drop('w_infowall_danmu_tpl');
	}

}
