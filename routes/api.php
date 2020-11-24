<?php

//use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/************************************ 微信公众号 服务交互 Api **************************************/
Route::any('wechatNotice/{token}', 'Wechat\WeChatController@index')->name('wechatNotice');


/************************************ Git coding webHook Api **************************************/
//Route::post('/uWei/webHook/php', 'LocalExec\CodingController@saveGitPull');
//Route::post('/uWei/webHook/vue', 'LocalExec\CodingController@saveGitPullVue');

/************************************ 运维服务稳定监控 **************************************/
Route::get('getWebState', 'Api\WebStateController@checkMysql');
