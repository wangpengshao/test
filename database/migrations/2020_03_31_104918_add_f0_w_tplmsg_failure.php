<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddF0WTplmsgFailure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('w_tplmsg_failure_third', function (Blueprint $table) {
            $table->string('errcode', 12)->nullable()->default('')->comment('错误码');
        });
        Schema::table('w_tplmsg_failure', function (Blueprint $table) {
            $table->string('errcode', 12)->nullable()->default('')->comment('错误码');
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
