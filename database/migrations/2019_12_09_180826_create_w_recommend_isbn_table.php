<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWRecommendIsbnTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('w_recommend_isbn', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('s_id')->comment('书单关联id');
			$table->integer('c_id')->nullable()->comment('收藏的关联id');
			$table->string('isbn')->comment('书箱编号');
			$table->string('reason')->nullable()->comment('推荐书籍的理由');
			$table->string('token', 20)->nullable();
			$table->integer('view_num')->nullable()->default(0)->comment('查看总数');
			$table->integer('col_num')->nullable()->default(0)->comment('书箱收藏总数');
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
		Schema::drop('w_recommend_isbn');
	}

}
