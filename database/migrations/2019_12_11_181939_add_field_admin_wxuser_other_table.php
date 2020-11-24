<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldAdminWxuserOtherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_wxuser_other', function (Blueprint $table) {
            $table->unsignedInteger('tplmsg_group_num')->default(0)->comment('模板消息群发次数');
            $table->unsignedInteger('custommsg_group_num')->default(0)->comment('客服消息群发次数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
