<?php

/*
|--------------------------------------------------------------------------
| Vue API Routes   (微门户前后端分离 Api)
|--------------------------------------------------------------------------
|
*/

/**************************************** 普通接口 ******************************************/
Route::middleware('RequiredToken')->group(function () {

    /* 1-1_授权 => config */
    Route::post('wechatConfig', 'Api\Micro\BasisController@vueConfig');

    /* 1-2_授权 => 微信登录 code 授权 */
    Route::get('fansAuthorization/uweiVue', 'Api\Main\AuthorizeController@vueFansCode');

    /* 1-3_授权 => openid 换取 密钥 */
    Route::get('openidAuthorization/uweiVue', 'Api\Main\AuthorizeController@vueFansOpenid');

    /* 3-2_信息 => 微信用户信息  */
    Route::get('wechat/getFansInfo', 'Wechat\FansController@getFansInfo');

    /* 3-6_信息 => 分馆明细列表 */
    Route::get('wechat/libSecondaryList', 'Api\Micro\PavilionController@libSecondaryList');

    /* 3-12_信息 => 借阅者排行榜 */
    Route::get('wechat/readTheCharts', 'Api\ReaderController@readTheCharts');

    /* 3-13_信息 => 图书借阅排行榜 */
    Route::get('wechat/bookTheCharts', 'Api\ReaderController@bookTheCharts');

    /* 5-1_wx配置 => 微信jssdk */
    Route::post('wechat/jssdkConfig', 'Api\Micro\BasisController@vueJsSdk');

    /* 6-1_页面配置 => 读者绑定界面 */
    Route::get('wechat/getBindWebConfig', 'Api\Micro\BasisController@bindPageLayout');

    /* 6-2_页面配置 => 首页轮播&菜单 */
    Route::get('wechat/getLunboMenu', 'Api\Micro\BasisController@vueHomePicture');

    /* 6-4_页面配置 => 新书通报 */
    Route::get('wechat/getnewbookList', 'Api\HomewechatController@newbookList');

    /* 6-5_页面配置 => 检索热词 */
    Route::get('wechat/getSearchHot', 'Api\HomewechatController@getSearchHot');

    /* 6-6_页面配置 => 资源类目 */
    Route::get('wechat/menuClassify', 'Api\Micro\BasisController@menuClassify');

    /* 6-7_页面配置 => 修改密码配置 */
    Route::get('/wechat/passwordConfig', 'Api\Micro\BasisController@pwdConfig');

    /* 8-1_活动平台 => 切卡数据 */
    Route::get('wechat/activity/getCardList', 'Api\ActivityController@activityCard');

    /* 8-3_活动平台 => 活动详情 */
    Route::get('wechat/activity/getDetails/{id}', 'Api\ActivityController@getDetails');

    /* 8-6_活动平台 => 活动筛选分类 */
    Route::get('wechat/activity/getCategory', 'Api\ActivityController@getCategory');

    /* 9-1_线上办证 => (实名)办证配置 */
    Route::get('wechat/certificate/lv2/config', 'Api\CertificateController@lv2config');

    /* 9-4_线上办证 => 发送短信验证码 */
    Route::get('wechat/sms/vueSendCode', 'Api\SmsController@vueSend');

    /* 9-5_线上办证 => 检查验证码 */
    Route::get('wechat/sms/vueCheckCode', 'Api\SmsController@vueCheck');

    /* 9-6_线上办证 => 检查手机归属地 */
    Route::get('/wechat/certificate/checkPhoneRegion', 'Api\CertificateController@checkPhoneRegion');

    /* 9-7_线上办证 => (普通)办证配置 */
    Route::get('/wechat/certificate/lv1/config', 'Api\CertificateController@lv1config');

    /* 10-5_财经流通 => 欠款支付配置 */
    Route::get('/wechat/financial/arrearsConfig', 'Api\FinancialController@arrearsConfig');

    /* 10-9_财经流通 => 代付欠款结果 */
    Route::get('wechat/df-financial/payArrearsStatus/{id}', 'Api\FinancialController@payDfArrearsStatus');

    /* 20-4_联盟认证 => 绑定页面配置 */
    Route::get('/wechat/union/pageConfig', 'Api\Union\BaseController@pageConfig');

    /*  问卷、答题 =》活动列表 */
    //Route::get('/wechat/questionnaire/questionnaireList', 'Api\QuestionnaireController@questionnaireList');

    /*  问卷、答题 =》题目列表 */
    //Route::get('/wechat/questionnaire/subjectList', 'Api\QuestionnaireController@subjectList');

    /* 问卷、答题 =》标准答案 */
    //Route::get('/wechat/questionnaire/loadStandardAnswer', 'Api\QuestionnaireController@loadStandardAnswer');

    /* 问卷、答题 =》用户成绩页面 */
    //Route::get('/wechat/questionnaire/userScore', 'Api\QuestionnaireController@userScore');

    /* 问卷、答题 =》答题排行榜 */
    //Route::get('/wechat/questionnaire/rankList', 'Api\QuestionnaireController@rankList');

});


/**************************************** 授权接口 ******************************************/
Route::middleware('multiauth:wechat')->group(function () {

    /* 1-0_授权 => 查看授权归属信息 */
    Route::post('/smallShortcut', 'Api\Micro\BasisController@smallShortcut');

    /* 2-1_服务操作 => 绑定读者 */
    Route::post('/wechat/openidBindReader', 'Api\Micro\BindReaderController@bindReader');

    /* 2-2_服务操作 => 检查绑定 */
    Route::get('/wechat/checkBindReader', 'Api\Micro\BindReaderController@checkBindReader');

    /* 2-3_服务操作 => 解除绑定 */
    Route::get('/wechat/removeBindReader', 'Api\Micro\BindReaderController@unBindReader');

    /* 2-4_服务操作 => 切换绑定 */
    Route::get('/wechat/toggleBindReader/id/{id}', 'Api\Micro\BindReaderController@toggleBindReader');

    /* 2-5_服务操作 => 删除证件 */
    Route::get('/wechat/deleteBindReader/id/{id}', 'Api\Micro\BindReaderController@deleteBindReader');

    /* 2-6_服务操作 => 添加证件 */
    Route::post('/wechat/addBindReader', 'Api\Micro\BindReaderController@addBindReader');

    /* 2-7_服务操作 => 续借书籍 */
    Route::get('/wechat/reader/renewbook/{barcode}', 'Api\Micro\ReaderController@renewbook');

    /* 2-8_服务操作 => 修改读者信息 */
    Route::post('/wechat/editReaderInfo', 'Api\HomewechatController@editReaderInfo');

    /* 2-10_服务操作 => 检索书目(Openlib) */
    Route::post('/wechat/bookSearchbib', 'Api\ReaderController@bookSearchbib');

    /* 2-11_服务操作 => 检索书目(Opac) */
    Route::post('/wechat/books/search', 'Api\Micro\BooksController@search');

    /* 2-12_服务操作 => 书目详情(Opac) */
    Route::get('/wechat/books/details/{bookrecno}', 'Api\Micro\BooksController@details');

    /* 2-13_服务操作 => 预借书籍 */
    Route::get('/wechat/registerprelend/{barcode}', 'Api\ReaderController@registerprelend');

    /* 2-14_服务操作 => 取消预借 */
    Route::get('/wechat/cancelprelend/{barcode}', 'Api\ReaderController@cancelprelend');

    /* 2-15_服务操作 => 预约书籍 */
    Route::get('/wechat/registerreserve/{barcode}', 'Api\ReaderController@registerreserve');

    /* 2-16_服务操作 => 取消预约 */
    Route::get('/wechat/cancelreserve/{barcode}', 'Api\ReaderController@cancelreserve');

    /* 2-17_服务操作 => 自助机登录 */
    Route::get('/wechat/selfService/scanQrCodeLogin/{id}', 'Api\SelfServiceController@scanQrCodeLogin');

    /* 2-18_服务操作 => 证件挂失 */
    Route::post('/wechat/readerLossCard', 'Api\ReaderController@lossCard');

    /* 2-19_服务操作 => 登录更新 */
    Route::get('/wechat/loginResetReader', 'Api\ReaderController@loginResetReader');

    /* 3-1_信息 => 个人绑定列表  */
    Route::get('/wechat/openidGetReader', 'Api\Micro\BindReaderController@openidGetBindList');

    /* 3-3_信息 => 绑定读者信息 */
    Route::get('/wechat/openidGetReaderInfo', 'Api\ReaderController@openidGetReaderInfo');

    /* 3-4_信息 => 读者当前借阅 */
    Route::get('/wechat/reader/currentLoan', 'Api\Micro\ReaderController@currentLoan');

    /* 3-5_信息 => 读者历史借阅 */
    Route::get('/wechat/reader/historyLoan', 'Api\Micro\ReaderController@historyLoan');

    /* 3-7_信息 => 读者当前&预借历史 */
    Route::get('/wechat/getSearchprelendlist', 'Api\HomewechatController@myPrelendList');

    /* 3-8_信息 => 读者当前&预约历史 */
    Route::get('/wechat/getSearchreslist', 'Api\HomewechatController@mySearchreslist');

    /* 3-9_信息 => 馆藏列表(预借&预约) */
    Route::get('/wechat/getBookHolding', 'Api\HomewechatController@getBookHolding');

    /* 3-10_信息 => 个人中心读者信息 */
    Route::get('/wechat/personalCenter/getReaderInfo', 'Api\Micro\ReaderController@readerInfo');

    /* 3-11_信息 => 读者二维码 */
    Route::get('/wechat/getReaderQrcode', 'Api\ReaderController@getReaderQrcode');

    /* 3-14_信息 => 个人推广二维码(限服务号) */
    Route::get('/wechat/getSeoQrCode', 'Api\WechatConfigController@getSeoQrCode');

    /* 3-15_信息 => 图文详情 */
    Route::get('/wechat/getImgContent/details/{id}', 'Api\Micro\BasisController@getImgContent');

    /* 3-16_信息 => 证绑定历史 */
    Route::get('/wechat/getReaderBindLog', 'Api\ReaderController@getReaderBindLog');

    /* 6-3_页面配置 => 首页猜你喜欢 */
    Route::get('/wechat/getyoulikeList', 'Api\HomewechatController@youlikeList');

    /* 7-1_首页操作 => 访问菜单 */
    Route::get('/wechat/visitMenu/{menuid}', 'Api\HomewechatController@visitMenu');

    /* 8-2_活动平台 => 活动列表 */
    Route::get('/wechat/activity/getList', 'Api\ActivityController@activityList');

    /* 8-4_活动平台 => 个人报名列表 */
    Route::get('/wechat/activity/getMyList', 'Api\ActivityController@getMyList');

    /* 8-5_活动平台 => 活动列表筛选 */
    Route::get('/wechat/activity/searchList', 'Api\ActivityController@searchList');

    /* 8-7_活动平台 => 活动报名 */
    Route::post('/wechat/activity/sendApply/{specialId}', 'Api\ActivityController@sendApply');

    /* 8-8_活动平台 => 取消报名 */
    Route::post('/wechat/activity/cancelApply/{specialId}', 'Api\ActivityController@cancelApply');

    /* 8-9_活动平台 => 报名信息(检查报名) */
    Route::get('/wechat/activity/readerAction/{specialId}', 'Api\ActivityController@readerAction');

    /* 8-10_活动平台 => 活动全部报名记录 */
    Route::get('/wechat/activity/getRecord/{specialId}', 'Api\ActivityController@getRecord');

    /* 8-11_活动平台 => 收藏活动 */
    Route::get('/wechat/activity/saveCollect/{specialId}', 'Api\ActivityController@saveCollect');

    /* 8-12_活动平台 => 收藏列表 */
    Route::get('/wechat/activity/collectList', 'Api\ActivityController@collectList');

    /* 8-13_活动平台 => 活动评论列表 */
    Route::get('/wechat/activity/contentList/{specialId}', 'Api\ActivityController@contentList');

    /* 8-14_活动平台 => 评论活动 */
    Route::post('/wechat/activity/saveContent', 'Api\ActivityController@saveContent');

    /* 9-2_线上办证 => (实名)提交申请 */
    Route::post('/wechat/certificate/lv2/save', 'Api\CertificateController@lv2save');

    /* 9-3_线上办证 => (实名)办证结果 */
    Route::get('/wechat/certificate/lv2/checkResult/{id}', 'Api\CertificateController@lv2CheckResult');

    /* 9-8_线上办证 => (普通)提交申请 */
    Route::post('/wechat/certificate/lv1/save', 'Api\CertificateController@lv1save');

    /* 9-9_线上办证 => (普通)办证结果 */
    Route::get('/wechat/certificate/lv1/checkResult/{id}', 'Api\CertificateController@lv1CheckResult');

    /* 9-10_线上办证 => 证号人脸验证 */
    Route::post('/wechat/certificate/checkFaceID', 'Api\CertificateController@checkFaceID');

    /* 9-11_线上办证 => 办证成功绑定 */
    Route::get('/wechat/certificate/afterSuccessful/bindReader', 'Api\CertificateController@afterSuccessful');

    /* 9-12_线上办证 => (普通)提交申请——工行聚合支付 */
    Route::post('/wechat/certificate/lv1/saveIcbc', 'Api\CertificateController@lv1saveIcbc');

    /* 10-1_财经流通 => 查询总欠款 */
    Route::get('/wechat/financial/getArrears', 'Api\FinancialController@getArrears');

    /* 10-2_财经流通 => 支付总欠款 */
    Route::get('/wechat/financial/payArrears', 'Api\FinancialController@payArrears');

    /* 10-3_财经流通 => 支付欠款结果 */
    Route::get('/wechat/financial/payArrearsStatus/{id}', 'Api\FinancialController@payArrearsStatus');

    /* 10-4_财经流通 => 我的欠款支付历史 */
    Route::get('/wechat/financial/payArrearsLog', 'Api\FinancialController@payArrearsLog');

    /* 10-6_财经流通 => 代付读者验证 */
    Route::post('/wechat/df-financial/checkGuest', 'Api\FinancialController@checkGuest');

    /* 10-7_财经流通 => 代付欠款列表 */
    Route::get('/wechat/df-financial/getDfArrears', 'Api\FinancialController@getDfArrears');

    /* 10-8_财经流通 => 代付欠款 (可多条) */
    Route::get('/wechat/df-financial/payDfArrears', 'Api\FinancialController@payDfArrears');

    /* 10-10_财经流通 => 代付支付历史 */
    Route::get('/wechat/financial/dfArrearsLog', 'Api\FinancialController@dfArrearsLog');

    /* 10-11_财经流通 => 支付总欠款——工行聚合支付 */
    Route::get('/wechat/financial/payArrearsIcbc', 'Api\FinancialController@payArrearsIcbc');


    //****************************** 联盟认证 ******************************//

    /* 20-1_联盟认证 => 检查绑定 */
    Route::get('/wechat/union/checkBind', 'Api\Union\BaseController@checkBind');

    /* 20-2_联盟认证 => 绑定读者 */
    Route::post('/wechat/union/bindReader', 'Api\Union\BaseController@bindReader');

    /* 20-3_联盟认证 => 解除绑定 */
    Route::get('/wechat/union/unBindReader/{id}', 'Api\Union\BaseController@unBindReader');

    /* 20-5_联盟认证 => 读者二维码 */
    Route::get('/wechat/union/readerCode', 'Api\Union\BaseController@readerCode');

    //****************************** 定制接口区 ******************************//

    /* 开采事业部 - 海南 - 提交办证申请接口 */
    Route::post('/wechat/custom/vue/haiNanCefSave', 'Api\Custom\VueController@haiNanCefSave');

    /* 问卷、答题 =》提交答题 */
    //Route::post('/wechat/questionnaire/submitReplyForExam', 'Api\QuestionnaireController@submitReplyForExam');

    /* 问卷、答题 =》提交问卷 */
    //Route::post('/wechat/questionnaire/submitReplyForSurvey', 'Api\QuestionnaireController@submitReplyForSurvey');

});
