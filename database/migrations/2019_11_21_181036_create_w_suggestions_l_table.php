<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSuggestionsLTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_suggestions_l', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('s_id')->nullable()->comment('类型关联id');
            $table->string('openid', 60)->nullable();
            $table->string('rdid', 20)->nullable();
            $table->string('name', 20)->nullable();
            $table->boolean('status')->default(0)->comment('留言进度');
            $table->string('headimgurl', 200)->nullable();
            $table->string('token', 30)->nullable();
            $table->string('title', 40)->nullable()->comment('标题');
            $table->string('tel', 11)->nullable()->comment('联系方式');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('info', 200)->default('')->comment('详情描述');
            $table->json('img')->nullable()->comment('图片');
            $table->json('other')->nullable()->comment('其它数据');
            $table->timestamps();
            $table->index(['rdid', 'openid'], 'openid_rdid');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('w_suggestions_l');
    }

}
