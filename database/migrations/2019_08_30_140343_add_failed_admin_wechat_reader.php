<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFailedAdminWechatReader extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_wechat_reader', function (Blueprint $table) {
            $table->string('origin_libcode', 15)->nullable()->comment('来源分馆代码');
            $table->string('origin_glc', 15)->nullable()->comment('来源全局馆ID');
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
