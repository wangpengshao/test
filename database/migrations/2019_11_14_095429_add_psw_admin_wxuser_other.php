<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPswAdminWxuserOther extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_wxuser_other', function (Blueprint $table) {
            $table->boolean('pw_check_sw')->default(0)->comment('检验开关');
            $table->unsignedTinyInteger('pw_min_length')->default(0)->comment('最小长度');
            $table->unsignedTinyInteger('pw_max_length')->default(0)->comment('最大长度');
            $table->unsignedTinyInteger('pw_type')->default(1)->comment('密码类型');
            $table->string('pw_prompt', 200)->nullable()->comment('提示');
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
