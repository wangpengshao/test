<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWCustommsgDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_custommsg_data', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 50)->index('token');
			$table->boolean('send_type')->default(0)->comment('群发方式，0：全部粉丝；1：分组；2：绑定用户；3：指定粉丝');
			$table->string('msg_type', 10)->default('0')->comment('消息类型，text：文本消息；image：图片消息；news：图文消息（跳转到外链）；mpnews：图文消息（跳转到图文消息页面）');
			$table->text('text_data')->nullable()->comment('文本消息数据');
			$table->text('image_data')->nullable()->comment('图片消息数据');
			$table->text('news_data')->nullable()->comment('图文消息数据');
			$table->text('mpnews_data')->nullable()->comment('图文消息数据');
			$table->boolean('status')->default(0)->comment('发送状态');
			$table->string('group_tag')->nullable()->comment('分组');
			$table->string('openids', 500)->nullable();
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
		Schema::drop('w_custommsg_data');
	}

}
