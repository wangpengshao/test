<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWRecommendSdTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_recommend_sd', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('书单标题');
            $table->string('token', 20)->nullable();
            $table->string('image', 250)->nullable()->default('')->comment('书单封面');
            $table->string('intro')->nullable()->comment('推荐简介');
            $table->integer('stage_id')->nullable()->default(0)->comment('第几期');
            $table->integer('view_num')->nullable()->default(0)->comment('书单查看数');
            $table->integer('col_num')->nullable()->default(0)->comment('书单收藏数');
            $table->string('isbn')->nullable()->comment('手动新增的isbn');
            $table->boolean('a_status')->nullable()->default(0)->comment('当前馆书单书籍添加至isbn表的状态（0 未添加  1已添加）');
            $table->boolean('c_status')->nullable()->default(0)->comment('收藏其它馆书单书籍至isbn表的状态（0 未收藏  1已收藏）');
            $table->boolean('status')->default('1')->comment('共享状态(1 开启 0 关闭)');
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
        Schema::drop('w_recommend_sd');
    }

}
