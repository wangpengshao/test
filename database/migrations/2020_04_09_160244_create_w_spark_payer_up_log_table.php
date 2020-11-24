<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWSparkPayerUpLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_spark_payer_up_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('c_id');
            $table->decimal('current_money', 10)->default(0.00)->comment('当前金额');
            $table->string('desc', 30)->nullable()->default('')->comment('描述');
            $table->integer('number')->default(0)->comment('余额');
            $table->dateTime('created_at')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('w_spark_payer_up_log');
    }

}
