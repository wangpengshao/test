<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    /***********************   省市区三级 start  ***********************/
    $router->resources([
        'china/province' => China\ProvinceController::class,
        'china/city' => China\CityController::class,
        'china/district' => China\DistrictController::class,
    ]);
    $router->get('api/china/city', 'China\ChinaController@city');
    $router->get('api/china/district', 'China\ChinaController@district');
    $router->get('china/cascading-select', 'China\ChinaController@cascading');
    //....获取身份证归属地区号
    $router->get('/wechat/certificateLog/api/getIdCardRegion', 'Wechat\TransactCardLv2Controller@getIdCardRegion');
    /***********************   省市区三级 end  ***********************/

    /***********************   公众号管理，用户授权，api授权  start  ***********************/
    $router->post('/accreditToken/{token?}', 'HomeController@accreditToken')->name('accreditToken'); //....授权session
    $router->resources([
        'wxuser' => WxuserController::class,                    //....公众号列表
        'newuser' => SecondaryuserController::class,             //....创建新用户(多级关系关联)
        'apiuseradmin' => Api\ApiuseradminController::class,      //....api授权管理中心
        'internal/relevanceMenu' => Wechat\RelevanceMenuController::class,  //.....内部业务拓展=>高级菜单管理
    ]);
    $router->post('apiuseradmin/userAlter', 'Api\ApiuseradminController@userAlter')->name('apiuseradmin.userAlter'); //....接口次数编辑
    $router->get('apiuseradmin/wxUser/search', 'Api\ApiuseradminController@searchWxuser')->name('apiuseradmin.searchW');
    $router->get('newuser/wxUser/search', 'SecondaryuserController@searchWxuser')->name('newuser.searchW');
    /***********************   公众号管理，用户授权，api授权  end  ***********************/

    /***********************   oss素材管理 start  ***********************/
    $router->get('ossMedia', 'OssMediaController@index')->name('ossMedia-index');
    $router->get('ossMedia/download', 'OssMediaController@download')->name('ossMedia-download');
    $router->delete('ossMedia/delete', 'OssMediaController@delete')->name('ossMedia-delete');
    $router->put('ossMedia/move', 'OssMediaController@move')->name('ossMedia-move');
    $router->post('ossMedia/upload', 'OssMediaController@upload')->name('ossMedia-upload');
    $router->post('ossMedia/folder', 'OssMediaController@newFolder')->name('ossMedia-new-folder');

    $router->post('ossMedia/wangEditorUpload', 'OssMediaController@wangEditorUpload')->name('wangEditorUpload'); //....wangEditorUpload
    $router->post('ossMedia/CKEditorUpload', 'OssMediaController@CKEditorUpload')->name('CKEditorUpload'); //....CKEditorUpload
    /***********************   oss素材管理 end  ***********************/

    /***********************   拓展页面 start ***********************/
    $router->get('/', 'Wechat\TcContentController@home')->name('tcContent-home'); //....首页
    $router->get('/internal/showTcContent/{id}', 'Wechat\TcContentController@showContent')->name('tcContent-show'); //....文章展示

    $router->get('/developInfo', 'HomeController@index');
    $router->resources([
        'internal/tcContent' => Wechat\TcContentController::class,  //.....内部业务拓展=>news管理
        'releaseCenter' => Wechat\ReleaseController::class,  //.....发布中心
    ]);

    /***********************   elasticsearch索引管理  ***********************/
    $router->get('elasticsearch/indexOperate', 'EsIndexController@index');
    $router->post('elasticsearch/indexOperate', 'EsIndexController@switchControl')->name('es.index.operate'); //索引开关控制

    /***********************   代发模板消息列表管理  ***********************/
    $router->get('oldPlatformTplMsgData/index', 'Wechat\MessageSends\OldPlatformTplMsgController@index')
        ->name('oldPlatformTplMsgData.index');
    $router->post('oldPlatformTplMsgData/resendTplMsg', 'Wechat\MessageSends\OldPlatformTplMsgController@resendTplMsg')
        ->name('oldPlatformTplMsgData.resendTplMsg'); //重新添加到发送队列

    /***********************   星火商户号 - 运营接口授权  ***********************/
    $router->resource('spark-payers', Wechat\Merchants\SparkPayerController::class);
    $router->post('spark-payers-balance', 'Wechat\Merchants\SparkPayerController@editBalance')->name('spark-payers.balance.edit'); //....余额编辑
    $router->get('spark-payers-balance/show-up-log', 'Wechat\Merchants\SparkPayerController@showUpLog')->name('spark-payers.up.log'); //....余额编辑记录
    $router->get('spark-payers-balance/show-use-log', 'Wechat\Merchants\SparkPayerController@showUseLog')->name('spark-payers.use.log'); //....余额使用记录

    /***********************   拓展页面 end ***********************/

});

Route::group([
    'prefix' => 'admin/wechat',
    'namespace' => 'App\Admin\Controllers\Wechat',
    'middleware' => ['web', 'admin', 'accredit'],
], function (Router $router) {
    $router->get('/sendrequest/menu', 'MenuController@sendMenu')->name('wechat.request.menu');       //生成微信菜单
    //关注自动回复
    $router->post('/passiveReply/subscribe', 'SubscribeReplyController@store')->name('wechat.passiveReply.sub.add');
    $router->post('/passiveReply/subscribe/{id}', 'SubscribeReplyController@update')->name('wechat.passiveReply.sub.up');
    $router->get('/passiveReply/subscribe', 'SubscribeReplyController@index');
    //回答不上配置
    $router->get('/passiveReply/authcontent', 'AuthcontentControllers@index');
    $router->post('/passiveReply/authcontent', 'AuthcontentControllers@store')->name('wechat.passiveReply.auth.add');
    $router->post('/passiveReply/authcontent/{id}', 'AuthcontentControllers@update')->name('wechat.passiveReply.auth.up');
    //绑定页面配置
    $router->post('/webconfig/bind', 'BindwebController@store')->name('wechat.webconfig.bind.add');
    $router->post('/webconfig/bind/{id}', 'BindwebController@update')->name('wechat.webconfig.bind.up');
    $router->get('/webconfig/bind', 'BindwebController@index');
    //粉丝列表
    $router->get('/fansList', 'FansController@index');
    $router->post('/fansList/fansAddTag', 'FansController@fansAddTag')->name('wechat.fans.fansAddTag');
    //粉丝列表->消息中心
    $router->get('/messageList', 'MessageController@index');
    $router->delete('/messageList/{id}', 'MessageController@del');
    //读者列表
    $router->get('/readerlist', 'ReaderController@index');
    $router->delete('/readerlist/{id}', 'ReaderController@destroy');
    //绑定记录
    $router->get('/binding/record', 'BindRecordController@index');
    //数据中心
    $router->get('/showData', 'ShowdataController@showA');
    //微信在线聊天界面
    $router->get('/chatRoom/{openid}', 'ChatRoomController@index')->name('chatroom.index');
    $router->post('/chatRoom/send/{openid}', 'ChatRoomController@saveAdminContent')->name('chatroom.send');
    //菜单数据统计
    $router->get('/menuLog/list', 'MenulogController@index');
    $router->get('/menuLog/show/{mid}', 'MenulogController@show')->name('menuLog.show');
    //拓展数据
    $router->get('/expandData', 'ExpandDataController@show')->name('ExpandData.show');
    //数据迁移
    $router->get('/oldUwei/dataMigration', 'DataMigrateController@index');
    $router->post('/oldUwei/dataMigration/update', 'DataMigrateController@update')->name('data.migration.up');

    //文章管理->分类
    $router->resources([
        '/config/menus' => MenuController::class,
        '/passiveReply/textcontent' => ContentReplyController::class,
        '/passiveReply/imgcontent' => ContentReplyController::class,
        '/wechatImage' => ImagewechatController::class,
        '/indexMenu' => IndexMenuController::class,
        '/artCategories' => ArtCategoriesController::class,
        '/Articles' => ArticlesController::class,
        '/groupList' => GroupController::class, //....粉丝分组管理
        '/menuClassify' => MenuBar\MenuClassifyController::class, //....资源菜单
    ]);

    $router->get('/templateMesList', 'TemplateMesController@index');    //....模版消息列表

    /**************************************** 抽奖系统 start ******************************************/
    $router->resource('/luckyDraw/prize', 'LuckyDraw\LuckyDrawPrizeController', ['names' => [
        'index' => 'luckyDraw.prize'
    ]]);//....奖品管理
    $router->resource('/luckyDraw/type01', 'LuckyDraw\LuckyDraw01Controller', ['names' => [
        'index' => 'luckyDraw.type01'
    ]]);//....大转盘
    $router->resource('/luckyDraw/type02', 'LuckyDraw\LuckyDraw02Controller', ['names' => [
        'index' => 'luckyDraw.type02'
    ]]);//....老虎机
    $router->resource('/luckyDraw/type03', 'LuckyDraw\LuckyDraw03Controller', ['names' => [
        'index' => 'luckyDraw.type03'
    ]]);//....砸金蛋
    $router->resources([
        '/luckyDraw/type01List' => LuckyDraw\LuckyDraw01ListController::class, //....抽奖列表
        '/luckyDraw/type02List' => LuckyDraw\LuckyDraw02ListController::class, //....抽奖列表
        '/luckyDraw/type03List' => LuckyDraw\LuckyDraw03ListController::class, //....抽奖列表
    ]);
    $router->post('/luckyDraw/type01List/addExpressNo', 'LuckyDraw\LuckyDraw01ListController@addExpressNo')->name('LuckyDraw01.addExpressNo');
    /**************************************** 抽奖系统 end ******************************************/

    /**************************************** 推广二维码 start ******************************************/
    $router->resources([
        '/qrCode/task' => QrTaskController::class, //....二维码任务管理
        '/qrCode/personal' => QrPersonalController::class, //....个人二维码管理
        '/qrCode/seo' => QrCodeSeoController::class, //....统计二维码管理
    ]);
    $router->get('/qrCode/config', 'QrCodeConfigController@index');
    $router->post('/qrCode/config', 'QrCodeConfigController@store')->name('wechat.qrCode.config.add');
    $router->post('/qrCode/config/{id}', 'QrCodeConfigController@update')->name('wechat.qrCode.config.up');
    /**************************************** 推广二维码 end ******************************************/

    /**************************************** 微信办证 start ******************************************/
    $router->resources([
        '/TransactType' => TransactTypeController::class, //....读者证类型
    ]);
    $router->get('/certificateLog', 'CertificateLogController@index'); //....办证列表
//    $router->get('/certificateLog/api/getIdCardRegion', 'TransactCardLv2Controller@getIdCardRegion');//....获取身份证归属地区号
    $router->post('/certificateLog/c/checkPay', 'CertificateLogController@checkPay')->name('wechat.certificate.checkPay');//....订单状态核对
    $router->post('/certificateLog/c/refundPay', 'CertificateLogController@refundPay')->name('wechat.certificate.refundPay');//....订单退款
    $router->post('/certificateLog/c/reapplyReader', 'CertificateLogController@reapplyReader')->name('wechat.certificate.reapplyReader');//异常补办
    $router->post('/certificateLog/c/auditReader', 'CertificateLogController@auditReader')->name('wechat.certificate.auditReader');//审核

    $router->get('/certificateLv2', 'TransactCardLv2Controller@index');//....实名办证
    $router->post('/certificateLv2/addData', 'TransactCardLv2Controller@store')->name('wechat.certificateLv2.add');
    $router->post('/certificateLv2/editData/{id}', 'TransactCardLv2Controller@update')->name('wechat.certificateLv2.up');
    $router->get('/certificateLv1', 'TransactCardLv1Controller@index');//....普通办证
    $router->post('/certificateLv1/addData', 'TransactCardLv1Controller@store')->name('wechat.certificateLv1.add');
    $router->post('/certificateLv1/editData/{id}', 'TransactCardLv1Controller@update')->name('wechat.certificateLv1.up');
    /**************************************** 微信办证 end ******************************************/

    /**************************************** 支付欠款 start ******************************************/
    $router->get('/financial/payArrears/config', 'ArrearsConfigController@index'); //....支付配置
    $router->post('/financial/payArrears/config', 'ArrearsConfigController@store')->name('wechat.arrears.add');
    $router->post('/financial/payArrears/config/{id}', 'ArrearsConfigController@update')->name('wechat.arrears.up');

    $router->get('/financial/payArrearsLog', 'PayArrearsLogController@index'); //....支付欠款列表
    $router->post('/financial/payArrearsLog/checkPay', 'PayArrearsLogController@checkPay')->name('wechat.arrears.checkPay');//....订单状态核对
    $router->post('/financial/payArrearsLog/refundPay', 'PayArrearsLogController@refundPay')->name('wechat.arrears.refundPay');//....订单退款
    $router->post('/financial/payArrearsLog/reapply', 'PayArrearsLogController@reapply')->name('wechat.arrears.reapply');//异常补销

    $router->get('/financial/df-payArrearsLog', 'DfArrearsLogController@index'); //....代付列表
    $router->post('/financial/df-payArrearsLog/checkPay', 'DfArrearsLogController@checkPay')->name('wechat.dfArrears.checkPay');//....订单状态核对
    $router->post('/financial/df-payArrearsLog/refundPay', 'DfArrearsLogController@refundPay')->name('wechat.dfArrears.refundPay');//....订单退款
    $router->post('/financial/df-payArrearsLog/reapply', 'DfArrearsLogController@reapply')->name('wechat.dfArrears.reapply');//异常补销
    $router->get('/financial/reconciliation/statistics', 'ReconStatisticsController@index')->name('wechat.dfArrears.statistics');//对账统计
    /**************************************** 支付欠款 end ******************************************/

    /**************************************** 集卡活动 start ******************************************/
    $router->resources([
        '/collectCard/index' => CollectCard\IndexController::class, //....集卡活动
        '/collectCard/cardConfig' => CollectCard\CardConfigController::class, //....卡片列表
        '/collectCard/cardTask' => CollectCard\CardTaskController::class, //....任务列表
        '/collectCard/prize' => CollectCard\CollectPrizeController::class, //....奖品列表
        '/collectCard/self-service' => CollectCard\SelfServiceController::class, //....自助机列表
    ]);
    $router->get('/collectCard/htmlConfig', 'CollectCard\HtmlConfController@index')->name('collectCard.html');
    $router->post('/collectCard/htmlConfig', 'CollectCard\HtmlConfController@store')->name('collectCard.html.add');
    $router->post('/collectCard/htmlConfig/{id}', 'CollectCard\HtmlConfController@update')->name('collectCard.html.up');
    $router->get('/collectCard/api/getUweiOriginType', 'CollectCard\CardTaskController@getUweiOriginType');//....内部二级类型
    $router->get('/collectCard/userList', 'CollectCard\UserController@index')->name('collectCard.userList');
    $router->get('/collectCard/userList/{id}', 'CollectCard\UserController@show')->name('collectCard.userView');
    $router->get('/collectCard/dataShow', 'CollectCard\UserController@dataShow')->name('collectCard.dataShow');
    $router->get('/collectCard/rewardData', 'CollectCard\UserController@rewardData')->name('collectCard.rewardData');
    /**************************************** 集卡活动 end ******************************************/

    /**************************************** 投票活动 start ******************************************/
    $router->resources([
        '/vote/config' => Vote\VoteConfigController::class, //....投票活动
        '/vote/group' => Vote\VoteGroupController::class, //....投票分组
        '/vote/items' => Vote\VoteItemsController::class, //....投票作品
        '/vote/message' => Vote\VoteMessageController::class, //....投票留言
        '/vote/blacklist' => Vote\VoteBlacklistController::class, //....投票黑名单
    ]);
    $router->get('/vote/top', 'Vote\VoteTopController@index')->name('vote.top'); //....排行榜
    $router->get('/vote/topExport', 'Vote\VoteTopController@topExport')->name('vote.topExport'); //....导出
    $router->post('/vote/auditing', 'Vote\VoteItemsController@auditing')->name('vote.auditing'); //....作品审核
    /**************************************** 投票活动 end ******************************************/

    /***********************   座位预约 start *********************/
    $router->resources([
        '/seat/config' => Seat\SeatConfigController::class, //....座位预约
        '/seat/seatRegion' => Seat\SeatRegionController::class, //....座位预约
        '/seat/seatAttr' => Seat\SeatAttrController::class, //....座位预约
        '/seat/seatChart' => Seat\SeatChartController::class, //....座位预约
        '/seat/seatUser' => Seat\SeatUserController::class, //....座位预约
    ]);

    $router->get('seat/initConfig', 'Seat\SeatConfigController@initConfig')->name('seat.initConfig');//....座位预约全局配置初始化
    $router->post('seat/postRegion', 'Seat\SeatRegionController@postRegion')->name('seat.postRegion');//....座位预约创建根区域
    $router->get('seat/charts/{id}', 'Seat\SeatChartController@charts')->name('seat.charts');//....座位预约生成座位
    $router->post('seat/chart/addAttr', 'Seat\SeatChartController@addAttr')->name('seat.chart.addAttr');//....座位属性添加
    $router->post('seat/chart/removeAttr', 'Seat\SeatChartController@removeAttr')->name('seat.chart.removeAttr');//....座位属性移除
    $router->get('seat/seatedLog', 'Seat\SeatUserController@seatedLog')->name('seat.seatUser.seatedLog');//....座位入座记录
    $router->get('seat/bookingCurr', 'Seat\SeatUserController@bookingCurr')->name('seat.seatUser.bookingCurr');//....座位当前预约记录
    $router->get('seat/bookingLog', 'Seat\SeatUserController@bookingLog')->name('seat.seatUser.bookingLog');//....座位预约历史记录
    $router->get('seat/chart/downloadQrcode', 'Seat\SeatChartController@downloadQrcode')->name('seat.downloadQrcode'); //座位二维码下载
    $router->post('seat/chart/changeStatus', 'Seat\SeatChartController@changeStatus')->name('seat.chart.changeStatus'); //修改座位状态
    $router->get('seat/data', 'Seat\SeatDataController@index')->name('seat.data'); //数据统计
    /***********************   座位预约 end   *********************/

    /***********************   电子卡包 start   *********************/

    $router->resources([
        '/card/member' => Card\MemberCardController::class, // 卡券——会员卡
    ]);
    $router->get('card/memberCardQrcode', 'Card\MemberCardController@getQrcode')->name('membercard.qrcode');    //卡券二维码
    $router->get('card/memberCardUsers', 'Card\MemberCardController@getUsers')->name('membercard.users');       //领卡用户
    /***********************   电子卡包 end   *********************/

    /***********************    问卷答题 start *********************/
//    $router->resources([
//        '/questionnaires/list' => Questionnaire\QuestionnaireController::class, // 问卷答题
//        '/questionnaires/question' => Questionnaire\QuestionController::class,  // 题目
//        '/questionnaires/examAnalyze' => Questionnaire\ExamAnalyzeController::class,      // 考卷分析
//        '/questionnaires/surveyAnalyze' => Questionnaire\SurveyAnalyzeController::class,  // 问卷分析
//    ]);
//    $router->get('/questionnaires/question/export', 'Questionnaire\QuestionController@export')->name('question.export');  //导出问卷题目
//    $router->get('examAnalyze/answerList', 'Questionnaire\ExamAnalyzeController@answerList')->name('examAnalyze.answerList');         //考题——用户答题列表
//    $router->get('examAnalyze/answerDetail', 'Questionnaire\ExamAnalyzeController@answerDetail')->name('examAnalyze.answerDetail');   //考题——用户答题详情
//    $router->put('examAnalyze/grade', 'Questionnaire\ExamAnalyzeController@grade')->name('examAnalyze.grade');                        //考题——答卷评分
//    $router->get('examAnalyze/analyze', 'Questionnaire\ExamAnalyzeController@analyze')->name('examAnalyze.analyze');                  //考题——题目答题分析
//
//    $router->get('surveyAnalyze/answerList', 'Questionnaire\SurveyAnalyzeController@answerList')->name('surveyAnalyze.answerList');         //问卷——用户答题列表
//    $router->get('surveyAnalyze/answerDetail', 'Questionnaire\SurveyAnalyzeController@answerDetail')->name('surveyAnalyze.answerDetail');   //问卷——用户答题详情
//    $router->get('surveyAnalyze/analyze', 'Questionnaire\SurveyAnalyzeController@analyze')->name('surveyAnalyze.analyze');                  //问卷——题目答题分析

    /***********************    问卷答题 end ***********************/

    /***********************    图书馆LBS定位 start ***********************/
    $router->get('libraryLbs/locationUnit', 'LibraryLbs\CompanyController@index')->name('wechat.libraryLbs.show');//....单位编辑
    $router->get('libraryLbs/locationUnit/list', 'LibraryLbs\CompanyController@list')->name('wechat.libraryLbs.list');//....单位列表
    $router->post('libraryLbs/locationUnit/add', 'LibraryLbs\CompanyController@store')->name('wechat.libraryLbs.add');
    $router->post('libraryLbs/locationUnit/edit/{id}', 'LibraryLbs\CompanyController@update')->name('wechat.libraryLbs.up');
    $router->get('libraryLbs/locationUnit/exportDome', 'LibraryLbs\CompanyController@exportDome')
        ->name('wechat.libraryLbs.exportDome');//....下载模版
    /***********************    图书馆LBS定位 end ***********************/

    /*********************** 区域共享 start ***********************/
//    $router->get('/share/regionalShare/{type}', 'Share\RegionalSharingController@index')->name('share.regionalShare.index'); //区域共享
//    $router->get('/share/regionalShare/{type}/{id}', 'Share\RegionalSharingController@view')->name('share.regionalShare.view'); //区域共享视图
//    $router->post('/share/regionalShare/eddit/{id}/{status}', 'Share\RegionalSharingController@edit')->name('share.regionalShare.edit'); //区域共享视图
//    $router->get('/share/storearticlestatus', 'Share\StoreArticleStatusController@index')->name('share.storearticlestatus.index'); //助力文章共享状态
//    $router->get('/share/storearticlestatus/{id}', 'Share\StoreArticleStatusController@edit')->name('share.storearticlestatus.index'); //助力文章共享状态
    /*********************** 区域共享 end ***********************/

    /*********************** 预约记录 start ***********************/
    $router->get('/deposit/reserve/record', 'Deposit\DepositController@index')->name('deposit.depositLog'); //....预约记录
    $router->post('/deposit/reserve/record/refundPay', 'Deposit\DepositController@refundPay')->name('deposit.refundPay'); //....申请退款
    $router->post('/deposit/reserve/record/cancel', 'Deposit\DepositController@cancel')->name('deposit.cancel'); //....取消退款
    $router->post('/deposit/reserve/record/overdue', 'Deposit\DepositController@overdue')->name('deposit.overdue'); //....逾约处理
    $router->post('/deposit/reserve/record/block', 'Deposit\DepositController@block')->name('deposit.block'); //....拉黑处理
    $router->post('/deposit/reserve/record/remove_block', 'Deposit\DepositController@remove_block')->name('deposit.remove_block'); //....解除拉黑
    $router->get('/deposit/order/census', 'Deposit\DepositController@census')->name('deposit.census');//预约统计

    $router->get('/deposit/config/setting', 'Deposit\DepositConfigController@index')->name('deposit.Config'); //....预约配置
    $router->post('/deposit/config/setting/{id}', 'Deposit\DepositConfigController@update')->name('deposit.Config.up'); //预约配置
    $router->get('/deposit/time_rules/setting', 'Deposit\DepositTimeRulesController@index')->name('deposit.timeRules.setting');//工作时间和黑名单设置
    $router->post('/deposit/time_rules/setting/config', 'Deposit\DepositTimeRulesController@config')->name('deposit.timeRules.setting.config');//工作时间和黑名单设置
    $router->get('/deposit/order/refund', 'Deposit\DepositOrderRefundController@index')->name('deposit.timeRules.order.refund');//预约退证
    /*********************** 预约记录 end ***********************/

    /*********************** 积分兑奖中心 start ***********************/
    $router->resources([
        '/IntegralExchange' => IntegralExchange\IntegralExchangeController::class,
    ]);
    /*********************** 积分兑奖中心 end ***********************/

    /*********************** 读者留言管理 start ***********************/
//    $router->resources([
//        '/suggestions/type' => Suggestions\SuggestionsTypeController::class,
//    ]);
//    $router->get('/suggestions/list', 'Suggestions\SuggestionsListController@index')->name('suggestions-list'); // 留言管理
//    $router->get('/suggestions/details', 'Suggestions\SuggestionsDetailController@index')->name('suggestions-details'); // 留言详情
//    $router->get('/suggestions/latestReply', 'Suggestions\SuggestionsMessagesController@index')->name('suggestions-reply'); // 最新回复
//    $router->post('/suggestions/send/{mid}/{sid}', 'Suggestions\SuggestionsDetailController@saveAdminContent')->name('chat-message.send'); // 保存管理员回复的信息
    /*********************** 读者留言管理 end ***********************/

    /*********************** 群发消息 start ***********************/
    $router->resources([
        '/templateMsgData/index' => MessageSends\CreateTplMsgController::class,
        '/customMsgData/index' => MessageSends\CreateCustomMsgController::class,
    ]);
    $router->post('/templateMsgData/previewTplMsg', 'MessageSends\CreateTplMsgController@previewTplMsg')->name('templateMsgData.previewTplMsg'); //发送预览
    $router->post('/templateMsgData/sendTplMsg', 'MessageSends\CreateTplMsgController@sendTplMsg')->name('templateMsgData.sendTplMsg'); //添加到发送队列
    $router->post('/customMsgData/previewCustomMsg', 'MessageSends\CreateCustomMsgController@previewCustomMsg')->name('customMsgData.previewCustomMsg'); //发送预览
    $router->post('/customMsgData/sendCustomMsg', 'MessageSends\CreateCustomMsgController@sendCustomMsg')->name('customMsgData.sendCustomMsg'); //添加到发送队列

    /*********************** 群发消息 end   ***********************/

    /*********************** 推荐书单 start ***********************/
//    $router->resource('/recommend/books', Recommend\RecommendBooksController::class); // 书单列表
//    $router->post('/recommend/saveIsbn', 'Recommend\RecommendBooksController@saveIsbn')->name('saveIsbn'); // 将手动添加的isbn数据添加到书库中
//    $router->get('/recommend/bookSquare', 'Recommend\BookSquareController@index')->name('bookSquare.index'); // 书单广场
//    $router->post('/recommend/bookSquare/collect', 'Recommend\BookSquareController@collect')->name('bookSquare.collect'); // 收藏书单
//    $router->get('/recommend/bookStatistics', 'Recommend\BookStatisticsController@index')->name('bookStatistics.index'); // 书单数据统计
//    $router->get('/recommend/messageList', 'Recommend\MessageListController@index')->name('message.list'); // 留言管理
//    $router->get('/recommend/messageDetails', 'Recommend\MessageDetailController@index')->name('message.details'); // 留言详情
//    $router->post('/recommend/messageSend/{mid}', 'Recommend\MessageDetailController@saveAdminContent')->name('message-info.send'); // 保存管理员回复的信息
//    $router->get('/recommend/bookList/{s_id}/{token}', 'Recommend\BookListController@index')->name('bookList.index'); // 书籍列表
//    $router->get('/recommend/bookList/{s_id}/{token}/{id}', 'Recommend\BookListController@show')->name('bookList.show'); // 书籍详情
    /*********************** 推荐书单 end ***********************/

    /*********************** 馆公告 start ***********************/
    $router->resource('/libNotice/index', LibNoticeController::class);
    /*********************** 馆公告 end ***********************/

    /*********************** 催还消息 start ***********************/
    $router->get('/expire-notices', 'Notice\ExpireNoticeController@index')->name('wechat.expire-notices');//基础配置
    $router->post('/expire-notices', 'Notice\ExpireNoticeController@store')->name('wechat.expire-notices.add');
    $router->put('/expire-notices/{id}', 'Notice\ExpireNoticeController@update')->name('wechat.expire-notices.up');

    $router->get('/notice-tasks', 'Notice\NoticeTaskController@index')->name('wechat.notice-tasks');//推送任务列表
    $router->get('/notice-tasks/{id}', 'Notice\NoticeTaskController@show');//推送任务详情字段
    $router->get('/notice-tasks/{id}/query-record', 'Notice\NoticeTaskController@queryRecord')->name('wechat.notice-tasks.query'); //推送任务记录查询
    $router->post('/notice-tasks/retry', 'Notice\NoticeTaskController@retry')->name('wechat.notice-tasks.retry');//手动重试任务
    /*********************** 催还消息 end ***********************/

    /*********************** 消息上墙 start ***********************/
    $router->resource('/infowall/config', 'InfoWall\InfoWallConfigController', ['names' => [
        'index' => 'infowall.config'
    ]]);//....活动列表
    $router->resource('/infowall/danmu/tpl', 'InfoWall\DanMuTplControlller', ['names' => [
        'index' => 'danmuTpl.index'
    ]]);//....弹幕模板
    $router->get('/infowall/tplShare', 'InfoWall\TplShareController@index')->name('tplShare.index'); // 共享模板
    $router->post('/infowall/addTpl', 'InfoWall\TplShareController@addTpl')->name('tplShare.addTpl'); // 添加共享模板
    $router->get('/infowall/userManage', 'InfoWall\UserManageController@index')->name('userManage.index'); // 用户管理
    $router->post('/infowall/userManage/pullBlack', 'InfoWall\UserManageController@pullBlack')->name('userManage.pullBlack'); // 拉黑用户
    $router->get('/infowall/newsList', 'InfoWall\NewsListController@index')->name('infowall.newsList'); // 用户消息列表
    $router->post('/infowall/newsList/shelf', 'InfoWall\NewsListController@shelf')->name('newsList.shelf'); // 下架留言信息
    $router->post('/infowall/newsList/check', 'InfoWall\NewsListController@check')->name('newsList.check'); // 批量审核
    $router->post('/infowall/newsList/singleCheck', 'InfoWall\NewsListController@singleCheck')->name('newsList.singleCheck'); // 单个审核
    /*********************** 消息上墙 end ***********************/

});


Route::group([
    'prefix' => 'admin/miniProgram',
    'namespace' => 'App\Admin\Controllers\Mini',
    'middleware' => ['web', 'admin'],
], function (Router $router) {

    //办证小程序
    $router->resources([
        '/certificate/config' => CertificateController::class,
        '/certificate/type' => CefTypeController::class,
        '/certificate/img' => CefImgController::class,
    ]);
    $router->match(['get', 'put'], 'certificate/config/{id}/pay', 'CertificateController@paySet')->name('certificate.paySet');
    $router->get('/certificateLog/index', 'CertificateLogController@index');                                                        //办证订单列表
    $router->post('/certificateLog/c/checkPay', 'CertificateLogController@checkPay')->name('mini.certificate.checkPay');            //订单状态核对
    $router->post('/certificateLog/c/refundPay', 'CertificateLogController@refundPay')->name('mini.certificate.refundPay');         //订单退款
    $router->post('/certificateLog/c/reapplyReader', 'CertificateLogController@reapplyReader')->name('mini.certificate.reapplyReader');//异常补办

});

//专题资源
Route::group([
    'prefix' => 'admin/specialColumn',
    'namespace' => 'App\Admin\Controllers\specialColumn',
    'middleware' => ['web', 'admin']
], function (Router $router) {
    $router->resources([
        '/redgevemment/config' => RedGevemmentController::class
    ]);
});

//阅读战疫
Route::group([
    'prefix' => 'admin/epidemicPrevention',
    'namespace' => 'App\Admin\Controllers\Wechat\EpidemicPrevention',
    'middleware' => ['web', 'admin']
], function (Router $router) {
    $router->resource('audit', AuditController::class);
});

/*********************** 素材库路由 ***********************/
Route::group([
    'prefix' => 'admin/wechat',
    'namespace' => 'App\Admin\Controllers\Wechat',
    'middleware' => ['web', 'admin', 'accredit'],
], function (Router $router) {
    $router->get('/imgList', 'PersonalstuffController@imgList')->name('wechat.imgList'); //....自单位素材库
    $router->get('/uweiIconList', 'PersonalstuffController@uweiIconList')->name('wechat.uweiIconList');//....图创素材库
    $router->get('/material', 'PersonalstuffController@index')->name('material-index'); //....微信素材库

});


