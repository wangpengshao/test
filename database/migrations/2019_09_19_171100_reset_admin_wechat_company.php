<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ResetAdminWechatCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //先删除表
        Schema::drop('admin_wechat_company');
        //重新创建表
        Schema::create('admin_wechat_company', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('token', 20)->default('')->index('token');
            $table->string('name', 30)->default('')->comment('单位名称');
            $table->boolean('is_show')->comment('是否显示');
            $table->timestamps();
            $table->string('logo', 150)->nullable()->default('')->comment('logo');
            $table->string('telephone', 20)->nullable()->default('')->comment('电话');
            $table->string('phone', 20)->nullable()->comment('手机');
            $table->integer('province_id')->nullable()->comment('省');
            $table->integer('city_id')->nullable()->comment('市');
            $table->integer('district_id')->nullable()->comment('区');
            $table->string('address', 30)->nullable()->comment('详细地址');
            $table->float('lat', 10, 6)->nullable()->default(0.000000)->comment('坐标');
            $table->float('lng', 10, 6)->nullable()->default(0.000000)->comment('坐标');
            $table->integer('p_id')->nullable()->default(0)->comment('父id');
            $table->integer('order')->nullable()->default(0)->comment('顺序');
            $table->string('intro', 300)->nullable()->comment('简介');
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
        Schema::drop('admin_wechat_company');
    }
}
