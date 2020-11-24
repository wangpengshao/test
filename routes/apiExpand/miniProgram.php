<?php

/************************************ 广图办证小程序Api **************************************/
Route::get('gtMiniProgram', 'Api\GtMiniPController@returnToken');//....返回token
Route::get('gtMiniProgram/cardBindUid', 'Api\GtMiniPController@cardBindUid');//....身份证绑定
Route::get('gtMiniProgram/cardUnBindUid', 'Api\GtMiniPController@cardUnBindUid');//....身份证解绑
Route::get('gtMiniProgram/uidFindCard', 'Api\GtMiniPController@uidFindCard');//....查询身份证
Route::get('gtMiniProgram/codeGetInfo', 'Api\GtMiniPController@codeGetInfo');//....session信息
Route::get('gtMiniProgram/getAccessToken', 'Api\GtMiniPController@getAccessToken');//....getAccessToken

/************************************ 川图办证小程序Api **************************************/
Route::post('scMiniProgram/save', 'Api\Mini\ScMiniController@save');                                //读者办证
Route::get('scMiniProgram/readerCertification', 'Api\Mini\ScMiniController@readerCertification');   //读者认证
Route::get('scMiniProgram/checkRdid', 'Api\Mini\ScMiniController@checkRdid');                      //检查读者证是否存在

/************************************ 通用版办证小程序Api v1 **************************************/
Route::group([
    'prefix' => 'miniProgram/certificate/v1',
    'namespace' => 'Api\Mini',
], function ($route) {
    $route->get('getConfig', 'CefMiniController@getConfig');                     //获取配置
    $route->get('getType', 'CefMiniController@getType');                         //读者类型
    $route->get('codeGetInfo', 'CefMiniController@codeGetInfo');                 //code换取用户信息
    $route->get('uidFindCard', 'CefMiniController@uidFindCard');                 //uid绑定信息
    $route->get('cardBindUid', 'CefMiniController@cardBindUid');                 //绑定证号
    $route->get('cardUnBindUid', 'CefMiniController@cardUnBindUid');             //解绑证号
    $route->post('readerInfo', 'CefMiniController@readerInfo');                  //读者认证(需要密码,返回读者信息)
    $route->post('readerInformation', 'CefMiniController@readerInformation');    //读者信息(免密码)
    $route->post('readerInformationLv2', 'CefMiniController@readerInformationLv2'); //读者信息(密码)
    $route->post('rdTypeChange', 'CefMiniController@readerTypeChange');          //修改读者类型
    $route->get('checkRdid', 'CefMiniController@checkRdid');                     //检查存在
    $route->post('save', 'CefMiniController@save');                              //办理证号
    $route->get('qrCode', 'CefMiniController@qrCode');                           //读者二维码
    $route->get('getAccessToken', 'CefMiniController@getAccessToken');           //accessToken
    $route->post('storeFansInfo', 'CefMiniController@storeFansInfo');            //存储粉丝信息
    $route->post('encryptField', 'CefMiniController@encryptField');              //加密
    $route->get('checkRepetition', 'CefMiniController@checkRepetition');         //检查存在
    $route->post('save2', 'CefMiniController@save2');                            //办理证号,返回证号信息
    $route->post('saveOrPay', 'CefMiniController@saveOrPay');                    //办证增加支付
    $route->get('CheckResult/{id}', 'CefMiniController@CheckResult');            //查询办证结果
});

/************************************ 电子资源小程序接口 v1 **************************************/
Route::group([
    'prefix' => 'miniProgram/e-resources/v1',
    'namespace' => 'Api\Mini',
    'middleware' => 'RequiredToken'
], function ($route) {
    $route->get('getAuthorizeToken', 'EResourcesController@getAuthorizeToken');     //授权
    $route->post('readerValidation', 'EResourcesController@readerValidation');      //读者验证
    $route->get('opacSearch', 'EResourcesController@opacSearch');                   //书目检索
    $route->get('bookDetails', 'EResourcesController@bookDetails');                 //书目详情
    $route->get('qrCode', 'EResourcesController@qrCode');                           //读者二维码
});

/************************************ 电子卡包 *****************************************/
Route::get('memberCard/getMemberCard', 'Api\MemberCardController@getMemberCard');                    //获取卡券
Route::post('memberCard/saveMemberCardUser', 'Api\MemberCardController@saveMemberCardUser');         //保存小程序领卡记录
