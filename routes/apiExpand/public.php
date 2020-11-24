<?php

/************************************ 外部授权开放 Api **************************************/
Route::get('/wechatAuthorization/public', 'Api\Main\PublicAuthorizeController@userAuth')->middleware('throttle:60,1');
//                'opBase' => 'openid (静默授权)',
//                'opInfo' => 'openid (网页授权)',
//                'fansInfo' => 'openid (粉丝信息)',
//                'opGetRe' => 'openid_读者',
//                'reGetOp' => '读者证_openid',
//                'bindRe' => '绑定读者',             暂无
//                'unbindRe' => '解绑读者',           暂无
//                'sendTemplate' => '模版消息',
//                'sendMessage' => '客服消息',        -
//                'getJdk' => '微信JDK配置',          -

//中间件说明  auth:api=>授权中间件 , throttle=>频率控制  , checkApiUser=>用户控制
Route::group([
    'prefix' => 'authorize/public',
    'namespace' => 'PublicApi',
    'middleware' => ['multiauth:api', 'throttle:rate_limit,1', 'checkApiUser'],
], function () {
    //token && code => 静默授权
    Route::get('getOpenidBase', 'OpenidController@getOpenidBase')->middleware(['scope:opBase']);
    //token && code => 网页授权
    Route::get('getOpenidInfo', 'OpenidController@getOpenidInfo')->middleware(['scope:opInfo']);
    //token && openid => 粉丝信息
    Route::post('getFansInfo', 'OpenidController@getFansInfo')->middleware(['scope:fansInfo']);
    //token && openid => 查询绑定读者
    Route::post('openidGetReader', 'OpenidController@openidGetReader')->middleware(['scope:opGetRe']);
    //token && rdid => 查询读者openid
    Route::post('readerGetOpenid', 'OpenidController@readerGetOpenid')->middleware(['scope:reGetOp']);
    //token && rdid =>  模版消息
    Route::post('sendTemplate', 'OpenidController@sendTemplate')->middleware(['scope:sendTemplate']);
    //token && openid =>  模版消息openid
    Route::post('sendTemplateForOpenid', 'OpenidController@sendTemplateForOpenid')->middleware(['scope:sendTemplateForOpenid']);
    //token && rdid =>  客服消息(文本)
    Route::post('sendMessage', 'OpenidController@sendMessage')->middleware(['scope:sendMessage']);
    Route::post('groupSendMessage', 'OpenidController@groupSendMessage')->middleware(['scope:groupSMes']);
    //token && rdid =>  客服消息(图文)
    Route::post('sendImgMes', 'OpenidController@sendImgMes')->middleware(['scope:sendImgMes']);
    Route::post('groupSendImgMes', 'OpenidController@groupSendImgMes')->middleware(['scope:groupSImgMes']);
    //token => Access Token
    Route::post('getAccessToken', 'ConfigController@getAccessToken')->middleware(['scope:getAcToken']);
    //token => Js Sdk
    Route::post('getJsSdk', 'ConfigController@getJsSdk')->middleware(['scope:getJsSdk']);
    //token => 微信活跃用户
    Route::get('getWechatActiveUsers', 'OpenidController@getActiveUser')->middleware(['scope:getActUser']);

});

/************************************ 集卡 api **************************************/
Route::get('collectCard/getSerial', 'Api\CCardController@getSerial')->middleware('RequiredToken');//....第三方集卡

/************************************ 公司内部非授权 api **************************************/
Route::get('company/zhq/getFansInfo', 'Api\CompanyController@zhqGetFansInfo');//....智慧墙=>粉丝数据
Route::get('company/opcs/bindReader', 'Api\CompanyController@internalBindReader');//....开采=>读者绑定
Route::get('company/general/getBindReader', 'Api\CompanyController@getBindReader');//....通用=>查询绑定
Route::get('company/smallShortcut/accessToken', 'Api\CompanyController@getAccessToken');//...."内部"-AccessToken
Route::get('company/smallShortcut/ticket', 'Api\CompanyController@getTicket');//...."内部"-ticket
Route::post('company/general/sendTemplateForOpenid', 'Api\CompanyController@sendTemplateForOpenid');//...."内部"-模版消息

/************************************ 内部定制接口 **************************************/

Route::get('csm/appLibSearch', 'PublicApi\SearchController@index');//....图书馆列表&详细信息
Route::get('csm/wxuser/wxConf', 'PublicApi\SearchController@wxConf');//....旧框架对应关联新框架的wxConf
Route::get('csm/fansInfo', 'PublicApi\SearchController@fansInfo');//...."内部"-粉丝信息获取接口


/************************************ 70 周年 红色专题资源 **************************************/
//语录资源列表
Route::get('redGevemment/resource/anaList', 'Api\RedGevemmentController@anaList');
//音视频分类
Route::get('redGevemment/resource/getAllResClass', 'Api\RedGevemmentController@allResClass');
//音视频资源列表
Route::get('redGevemment/resource/resdataByClass', 'Api\RedGevemmentController@resdataByClass');
//电子书分类
Route::get('redGevemment/resource/getAllEbookCategory', 'Api\RedGevemmentController@getAllEbookCategory');
//电子书资源列表
Route::get('redGevemment/resource/ebookdata', 'Api\RedGevemmentController@ebookdata');
//精品推荐
Route::get('redGevemment/resource/getRecoRes', 'Api\RedGevemmentController@getRecoRes');
//获取授权用户信息
Route::get('redGevemment/resource/getConfig', 'Api\RedGevemmentController@getConfig');


/*********************************  代理旧版U微平台消息群发  *********************************/
Route::post('MsgSends/sendTplMsg', 'Api\MsgSendsController@sendTplMsg');

/*********************************  星火商户号 - 运营接口 - 企业付款零钱 *********************************/
Route::post('wechatMerchants/payerPocketMoney', 'PublicApi\MerchantsController@payerPocketMoney');
