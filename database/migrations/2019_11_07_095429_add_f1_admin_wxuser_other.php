<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddF1AdminWxuserOther extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_wxuser_other', function (Blueprint $table) {
            $table->boolean('vue_nav_sw')->default(1)->comment('微门户导航开关');
            $table->unsignedTinyInteger('appointment_min_day')->default(7)->comment('最小预约取消');
            $table->unsignedTinyInteger('appointment_max_day')->default(30)->comment('最大预约取消');
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
