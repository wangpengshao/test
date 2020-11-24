<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSuggestionsMTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_suggestions_m', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('m_id')->comment('关联留言id');
            $table->integer('s_id')->nullable()->comment('留言类型');
            $table->boolean('r_id')->nullable()->comment('分辨是读者或管理员回复的信息--标记（1 代表读者; 2 代表管理员）');
            $table->string('r_reply', 200)->nullable()->default('')->comment('读者回复信息');
            $table->string('a_reply', 200)->nullable()->default('')->comment('管理员回复信息');
            $table->string('token', 50)->nullable()->default('');
            $table->boolean('is_reading')->default(0)->comment('读者回复信息是否已读状态');
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
        Schema::drop('w_suggestions_m');
    }

}
