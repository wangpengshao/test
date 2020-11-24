<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWLuckydrawAddressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_luckydraw_address', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('p_id')->nullable()->comment('关联奖品列表的id');
			$table->string('token', 100)->nullable();
			$table->integer('draw_type')->unsigned()->comment('抽奖类型(1 幸运大转盘; 2 老虎机; 3 砸金蛋)');
			$table->string('name', 100)->nullable()->default('')->comment('收件人');
			$table->string('phone', 20)->nullable()->default('')->comment('电话');
			$table->string('address')->nullable()->default('')->comment('详细地址');
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
		Schema::drop('w_luckydraw_address');
	}

}
