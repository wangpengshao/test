<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFAdminWechatWebBind extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_wechat_web_bind', function (Blueprint $table) {
            $table->string('l_title', 15)->nullable()->comment('左超链文本');
            $table->string('l_link', 250)->nullable()->comment('左超链链接');
            $table->string('r_title', 15)->nullable()->comment('右超链文本');
            $table->string('r_link', 250)->nullable()->comment('右超链链接');
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
