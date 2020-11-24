<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFAdminWechatLuckydraw extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wechat_luckydraw_01', function (Blueprint $table) {
            $table->string('share_title', 20)->nullable()->comment('分享标题');
            $table->string('share_desc', 30)->nullable()->comment('分享描述');
            $table->string('share_img', 256)->nullable()->comment('分享封面');
        });
        Schema::table('wechat_luckydraw_02', function (Blueprint $table) {
            $table->string('share_title', 20)->nullable()->comment('分享标题');
            $table->string('share_desc', 30)->nullable()->comment('分享描述');
            $table->string('share_img', 256)->nullable()->comment('分享封面');
        });
        Schema::table('wechat_luckydraw_03', function (Blueprint $table) {
            $table->string('share_title', 20)->nullable()->comment('分享标题');
            $table->string('share_desc', 30)->nullable()->comment('分享描述');
            $table->string('share_img', 256)->nullable()->comment('分享封面');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
