<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSafeguardCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_safeguard_comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('token', 20)->nullable();
			$table->string('nickname', 20)->nullable();
			$table->string('openid', 50)->nullable();
			$table->string('headimgurl', 250)->nullable()->default('')->comment('封面');
			$table->timestamps();
			$table->string('content', 250)->nullable()->default('')->comment('内容');
			$table->integer('like_n')->nullable()->default(0)->comment('点赞');
			$table->boolean('status')->nullable()->default(0)->comment('状态');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('w_safeguard_comments');
	}

}
