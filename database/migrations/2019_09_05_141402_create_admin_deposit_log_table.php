<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminDepositLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_deposit_log', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('deposit_id')->unsigned();
			$table->string('token', 100)->index('token');
			$table->string('rdid', 50)->index('rdid');
			$table->string('name', 30);
			$table->string('idCard', 18)->comment('身份证');
			$table->string('phone', 11)->nullable()->comment('联系方式');
			$table->decimal('deposit', 10)->unsigned()->comment('押金');
			$table->boolean('from')->comment('来源，1：微信；2：pc；3：电话；4：现场');
			$table->boolean('status')->default(0)->comment('0：待处理；1：退款；2：逾约；3：取消; 4：拉黑');
			$table->boolean('client_status')->default(0)->comment('用户操作，0：未删除；1：删除');
			$table->date('yuyue_date')->comment('预约日期');
			$table->time('yuyue_time')->comment('预约时间');
			$table->integer('create_time')->unsigned();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_deposit_log');
	}

}
