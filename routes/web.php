<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/************************************ 旧U微 音频转链接上传OSS **************************************/
Route::post('/oldSystem/audio/upload', 'OldSystemController@store')->name('oldSystemUpload');

/************************************ U微 Web **************************************/
Route::get('/', 'HomeController@index');
Route::get('/LongTest', 'Web\TestController@test');

Route::get('/MP_verify_{verify}.txt', 'HomeController@txtVerify');

Route::match(['get', 'post'], '/internal/searchWxuser', 'HomeController@searchWxuser')->name('public::searchWxuser');

/************************************ U微 Web 正式 **************************************/
//大转盘
Route::get('/webWechat/luckyDraw/type01', 'Web\LuckyDraw\Web01Controller@index')->name('LuckyDraw01::home');
Route::post('/webWechat/luckyDraw/type01/saveGather', 'Web\LuckyDraw\Web01Controller@saveGather')->name('LuckyDraw01::saveGather');
Route::get('/webWechat/luckyDraw/myRecord01', 'Web\LuckyDraw\Web01Controller@myRecord')->name('LuckyDraw01::myRecord');
Route::get('/webWechat/luckyDraw/type01/addAddress', 'Web\LuckyDraw\Web01Controller@addAddress')->name('LuckyDraw01::addAddress'); // 增加地址信息页面
Route::post('/webWechat/luckyDraw/type01/saveAddress', 'Web\LuckyDraw\Web01Controller@addAddress')->name('LuckyDraw01::saveAddress'); // 增加地址信息
Route::post('/webWechat/luckyDraw/type01/toDraw', 'Web\LuckyDraw\Web01Controller@toDraw')->name('LuckyDraw01::toDraw');
//拼人品老虎机
Route::get('/webWechat/luckyDraw/type02', 'Web\LuckyDraw\Web02Controller@index')->name('LuckyDraw02::home');
Route::post('/webWechat/luckyDraw/type02/saveGather', 'Web\LuckyDraw\Web02Controller@saveGather')->name('LuckyDraw02::saveGather');
Route::get('/webWechat/luckyDraw/myRecord02', 'Web\LuckyDraw\Web02Controller@myRecord')->name('LuckyDraw02::myRecord');
Route::get('/webWechat/luckyDraw/type02/addAddress', 'Web\LuckyDraw\Web02Controller@addAddress')->name('LuckyDraw02::addAddress'); // 增加地址信息页面
Route::post('/webWechat/luckyDraw/type02/saveAddress', 'Web\LuckyDraw\Web02Controller@addAddress')->name('LuckyDraw02::saveAddress'); // 增加地址信息
Route::post('/webWechat/luckyDraw/type02/toDraw', 'Web\LuckyDraw\Web02Controller@toDraw')->name('LuckyDraw02::toDraw');
//砸金蛋
Route::get('/webWechat/luckyDraw/type03', 'Web\LuckyDraw\Web03Controller@index')->name('LuckyDraw03::home');
Route::post('/webWechat/luckyDraw/type03/saveGather', 'Web\LuckyDraw\Web03Controller@saveGather')->name('LuckyDraw03::saveGather');
Route::get('/webWechat/luckyDraw/myRecord03', 'Web\LuckyDraw\Web03Controller@myRecord')->name('LuckyDraw03::myRecord');
Route::get('/webWechat/luckyDraw/type03/addAddress', 'Web\LuckyDraw\Web03Controller@addAddress')->name('LuckyDraw03::addAddress'); // 增加地址信息页面
Route::post('/webWechat/luckyDraw/type03/saveAddress', 'Web\LuckyDraw\Web03Controller@addAddress')->name('LuckyDraw03::saveAddress'); // 增加地址信息
Route::post('/webWechat/luckyDraw/type03/toDraw', 'Web\LuckyDraw\Web03Controller@toDraw')->name('LuckyDraw03::toDraw');

//********************************* 集卡活动 *********************************//
Route::group([
    'prefix' => 'webWechat/collectCard',
    'namespace' => 'Web\CollectCard',
    'as' => 'CollectCard::',
], function () {
    Route::get('index', 'IndexController@index')->name('index');
    Route::get('firstTime', 'IndexController@firstTime')->name('firstTime');
    Route::get('myCard', 'IndexController@myCard')->name('myCard');
    Route::get('rule', 'IndexController@rule')->name('rule');
    Route::get('strategy', 'IndexController@strategy')->name('strategy');
    Route::get('showAlert', 'IndexController@showAlert')->name('showAlert');
    Route::match(['get', 'post'], 'selfService', 'IndexController@selfService')->name('selfService');

    Route::get('share', 'ShareController@share')->name('share');
    Route::get('shareGiveCard', 'ShareController@shareGiveCard')->name('shareGiveCard');
    Route::post('shareGiveCard', 'ShareController@getGiveCard')->name('getGiveCard');
    Route::post('getShareTaskCard', 'ShareController@getShareTaskCard')->name('getShareTaskCard');

    Route::get('channel', 'OperateController@channel')->name('channel');
    Route::post('getAward', 'OperateController@getAward')->name('getAward');
    Route::get('checkSerial', 'OperateController@checkSerial')->name('checkSerial');
    Route::post('exchangeCard', 'OperateController@exchangeCard')->name('exchangeCard');

    Route::post('initRedPage', 'OperateController@initRedPage')->name('initRedPage');
});

//红包代发链接
Route::match(['get', 'post'], '/webWechat/getRedPage/{wmCode}', 'Web\RedPage\UnifiedController@index')->name('RedPage::unifiedGet');
Route::post('/webWechat/getRedPageSendMes', 'Web\RedPage\UnifiedController@sendMes')->name('RedPage::sendMes');


//*********************************预约系统 *********************************//
Route::group([
    'prefix' => 'webWechat/SeatBooking',
    'namespace' => 'Web\SeatBooking',
    'as' => 'Seat::'
], function () {
    Route::get('index', 'IndexController@index')->name('index'); //预约首页
    Route::get('startBooking', 'IndexController@startBooking')->name('startBooking');  //开始预约 step1
    Route::get('startBookingTwo', 'IndexController@startBookingTwo')->name('startBookingTwo');  //开始预约 step2
    Route::get('startBookingThree', 'IndexController@startBookingThree')->name('startBookingThree');  //开始预约 step3
    Route::get('modifyStepTwo', 'IndexController@modifyStepTwo')->name('modifyStepTwo');  //开始预约-修改 step2
    Route::get('modifyStepTree', 'IndexController@modifyStepTree')->name('modifyStepTree');  //开始预约-修改 step3
    Route::get('initSeat', 'IndexController@initSeat')->name('initSeat');  //初始座位
    Route::post('submitBooking', 'IndexController@submitBooking')->name('submitBooking');  //提交预约
    Route::post('updateBooking', 'IndexController@updateBooking')->name('updateBooking');  //修改预约
    Route::get('scoreLog', 'IndexController@scoreLog')->name('scoreLog');  //积分记录
    Route::get('seatLogList', 'IndexController@seatLogList')->name('seatLogList');  //入座记录seatBookingLog
    Route::get('seatBookingLog', 'IndexController@seatBookingLog')->name('seatBookingLog');  //预约记录
    Route::get('seatBookingStatus', 'IndexController@seatBookingStatus')->name('seatBookingStatus');  //预约状态 cancelBooking
    Route::post('cancelBooking', 'IndexController@cancelBooking')->name('cancelBooking'); //取消预约
    Route::match(['get', 'post'], 'signQrcode', 'IndexController@signQrcode')->name('signQrcode'); // 签到二维码
    Route::match(['get', 'post'], 'bookingAttendance', 'IndexController@bookingAttendance')->name('bookingAttendance'); // 签到
    Route::get('chartStatus', 'IndexController@chartStatus')->name('chartStatus'); //座位详情
    Route::post('seatUseAjax', 'IndexController@seatUseAjax')->name('seatUseAjax'); //入座
    Route::post('seatQueue', 'IndexController@seatQueue')->name('seatQueue'); //入座排队
    Route::post('seatLogoffAjax', 'IndexController@seatLogoffAjax')->name('seatLogoffAjax'); //离座
    Route::post('seatFanhuiAjax', 'IndexController@seatFanhuiAjax')->name('seatFanhuiAjax'); //清空排队
    Route::get('timingTask', 'IndexController@timingTask')->name('timingTask'); //定时任务
});

//********************************* 投票系统 *********************************//
Route::group([
    'prefix' => 'webWechat/vote',
    'namespace' => 'Web\Vote',
    'as' => 'Vote::',
], function () {
    Route::get('index', 'IndexController@index')->name('index');         //首页
    Route::get('ajaxItems', 'IndexController@ajaxItems')->name('ajaxItems');    //首页作品异步
    Route::get('rank', 'IndexController@rankList')->name('rank');        //排行榜
    Route::get('explain', 'IndexController@explain')->name('explain');        //说明
    Route::get('details', 'IndexController@details')->name('details');        //详情
    Route::match(['get', 'post'], 'signUp', 'IndexController@signUp')->name('signUp');        //报名
    Route::post('ajaxVote', 'IndexController@ajaxVote')->name('ajaxVote');        //投票

    Route::get('index2', 'NewIndexController@index')->name('index2');                       //首页
    Route::get('ajaxItems2', 'NewIndexController@ajaxItems')->name('ajaxItems2');           //首页作品异步
    Route::get('details2', 'NewIndexController@details')->name('details2');                 //详情
    Route::get('rank2', 'NewIndexController@rankList')->name('rank2');                      //排行榜
    Route::get('explain2', 'NewIndexController@explain')->name('explain2');                 //说明
    Route::post('comment2', 'NewIndexController@comment')->name('comment2');                //评论
    Route::get('ajaxComment2', 'NewIndexController@ajaxComment')->name('ajaxComment2');     //评论列表


    Route::match(['get', 'post'], 'signUp2', 'NewIndexController@signUp')->name('signUp2'); //报名
    Route::post('ajaxVote2', 'NewIndexController@ajaxVote')->name('ajaxVote2');             //投票
});

/*********************** 预约记录wap ***********************/
Route::group([
    'prefix' => 'webWechat/deposit',
    'namespace' => 'Web\Deposit\Wap',
    'as' => 'Refund::',
], function () {
    Route::get('wap/order/refund/{token}', 'DepositOrderRefundController@index')->name('deposit.wap.index');
    Route::get('wap/order/refund/getReadersByIdcard/{idCard}/{token}', 'DepositOrderRefundController@getReadersByIdcard')->name('deposit.wap.getReader');//获取读者信息
    Route::get('wap/order/refund/depositLog/{token}', 'DepositOrderRefundController@depositLog')->name('deposit.wap.depositLog');//获取预约信息
    Route::get('wap/order/refund/depositLog/{token}/{rdid}', 'DepositOrderRefundController@depositLog')->name('deposit.wap.depositLog');//获取预约信息
});
/*********************** 预约记录PC ***********************/
Route::group([
    'prefix' => 'webWechat/deposit',
    'namespace' => 'Web\Deposit\User',
    'as' => 'Refund::',
], function () {
    Route::get('user/order/refund/{token}', 'DepositOrderRefundController@index')->name('deposit.user.index');
    Route::post('user/order/refund/getReadersByIdcard', 'DepositOrderRefundController@getReadersByIdcard')->name('deposit.user.getReader');//获取读者信息
    Route::post('user/order/refund/ajaxIndex/{token}', 'DepositOrderRefundController@ajaxIndex')->name('deposit.user.order.time');//获取时间
    Route::post('user/order/refund/ajaxGetMoney', 'DepositOrderRefundController@ajaxGetMoney')->name('deposit.user.ajaxGetMoney');//获取读者信息
    Route::post('user/order/refund/subscribe', 'DepositOrderRefundController@subscribe')->name('deposit.user.subscribe');//预约
    Route::post('user/order/refund/record/{id}', 'DepositOrderRefundController@record')->name('deposit.user.record');//查询预约
    Route::post('user/order/refund/cancelDeposit', 'DepositOrderRefundController@cancelDeposit')->name('deposit.user.subscribe');//取消预约
});

/*********************** 图书馆LBS定位 ***********************/
Route::get('webWechat/lbsLocation/index', 'Web\LibraryLbs\BranchLibController@index')->name('LbsLocation::index');

/*********************** 推广之星前端页面(临时) ***********************/
Route::get('webWechat/invitationStar/index', 'Web\InvitationStar\IndexController@index')->name('InvitationStar::index');

/*********************** 积分兑换中心 ***********************/
Route::get('/webWechat/IntegralAward/index', 'Web\IntegralAward\IntegralExchangeController@index')->name('Award::index');// 兑奖首页
Route::post('/webWechat/IntegralAward/matchPrize', 'Web\IntegralAward\IntegralExchangeController@matchPrize')->name('Award::matchPrize');// 兑奖操作
Route::post('/webWechat/IntegralAward/saveGather', 'Web\IntegralAward\IntegralExchangeController@saveGather')->name('Award::saveGather');// 保存兑奖人信息
Route::get('/webWechat/IntegralAward/awardRecord', 'Web\IntegralAward\IntegralExchangeController@awardRecord')->name('AwardRecord::home');// 兑奖记录页面

Route::get('/webWechat/sundry/btnText', 'Web\SundryController@btnText');

//********************************* 防疫指南 *********************************//
Route::get('/webWechat/epidemicPrevention', 'Web\Custom\SafeguardController@index');  // 首页
Route::match(['get', 'post'],
    '/webWechat/epidemicPrevention/ajaxComments',
    'Web\Custom\SafeguardController@ajaxComments'
)->name('epidemicPrevention::ajax');  // 评论
Route::post(
    '/webWechat/epidemicPrevention/like',
    'Web\Custom\SafeguardController@saveLike'
)->name('epidemicPrevention::like');   // 点赞

//********************************* 定制页面(兼容页面) *********************************//
Route::get('/webWechat/custom/rankingList', 'Web\Custom\OrdinaryController@rankingList');  //....图书借阅排行榜(检索接口)
Route::get('/webWechat/replaceFun', 'Web\Custom\FunController@index');  //....兼容旧框架FunAction

//Route::match(['get', 'post'], '/webWechat/custom/hbLibRegister', 'Web\Custom\OrdinaryController@hbLibRegister');  //....湖北省馆读者注册


/*********************** 消息上墙(许愿墙) start ***********************/
Route::group([
    'prefix' => 'webWechat/infowall',
    'namespace' => 'Web\InfoWall',
    'as' => 'Refund::',
], function () {
    Route::post('getDanmu', 'IndexController@getDanmu')->name('infowall.getDanmu');//获取弹幕
    Route::post('addUserInfo', 'IndexController@addUserInfo')->name('infowall.addUserInfo');//添加保存用户信息
    Route::post('getTopic', 'IndexController@getTopic')->name('infowall.getTopic');//获取二级话题
    Route::post('page/getTopic', 'IndexController@pageTopic')->name('infowall.page'); // 获取分页数据
    Route::post('wishWord/saveWish', 'IndexController@saveWish')->name('infowall.saveWish');//保存心愿
});
Route::get('/webWechat/infowall/index', 'Web\InfoWall\IndexController@index')->name('infowall.index');
Route::get('/webWechat/infowall/largeScreen', 'Web\InfoWall\IndexController@largeScreen')->name('infowall.largeScreen');// 大屏幕信息展示
Route::post('/webWechat/infowall/largeScreen/getDanmu', 'Web\InfoWall\IndexController@screenGetDanmu')->name('largeScreen.getDanmu');// 大屏幕获取弹幕
/*********************** 消息上墙(许愿墙) end ***********************/