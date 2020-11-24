<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddF0WTplmsgData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('w_tplmsg_data', function (Blueprint $table) {
            $table->integer('reality_n')->nullable()->default(0)->comment('成功数量');
            $table->integer('failure_n')->nullable()->default(0)->comment('失败数量');
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
