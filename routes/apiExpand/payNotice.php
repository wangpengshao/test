<?php

/************************************ 微信支付回调 Api **************************************/
/** 订单生成DEMO 'BZYJ_' . date('YmdH') . Str::uuid()->getNodeHex(). str_random(4)  **/

Route::post('wxPayNotice/{token}/default', 'Wechat\WxPayController@index')->name('wxPay_default');

Route::post('wxPayNotice/{token}/certificateLv1', 'Wechat\WxPayController@certificateLv1')->name('wxPay_certificateLv1');//....普通办证
Route::post('wxPayNotice/{token}/certificateLv2', 'Wechat\WxPayController@certificateLv2')->name('wxPay_certificateLv2');//....实名办证
Route::post('miniPayNotice/{token}/certificateLv1', 'Api\Mini\MiniPayController@certificateLv1')->name('miniPay_certificateLv1');//....小程序实名办证
Route::post('miniPayNotice/{token}/refundCertificate', 'Api\Mini\MiniPayController@certificate')->name('miniRefund_certificate');//....小程序实名办证退款

Route::post('wxPayNotice/{token}/refundCertificate', 'Wechat\WxRefundController@certificate')->name('WxRefund_certificate');//....办证退款
Route::post('wxPayNotice/{token}/refundPayArrears', 'Wechat\WxRefundController@payArrears')->name('WxRefund_payArrears');//....支付欠款退款
Route::post('wxPayNotice/{token}/refundDfArrears', 'Wechat\WxRefundController@dfArrears')->name('WxRefund_dfArrears');//....支付欠款退款

//Route::post('wxPayNotice/{token}/advancePayment', 'Wechat\WxPayController@advancePayment')->name('wxPay_advancePayment');//....预付款充值
Route::post('wxPayNotice/{token}/payArrears', 'Wechat\WxPayController@payArrears')->name('wxPay_payArrears');//....支付总欠款
Route::post('wxPayNotice/{token}/payDfArrears', 'Wechat\WxPayController@payDfArrears')->name('wxPay_dfArrears');//....代付欠款

/************************************ 工行聚合支付回调 Api **************************************/
Route::post('aggregatePayment/{token}/default', 'Wechat\AggregatePaymentController@icbcNotify')->name('aggregatePayment_default');//....支付欠款
Route::post('aggregatePayment/{token}/certificateLv1', 'Wechat\AggregatePaymentController@certificateLv1')->name('aggregatePayment_certificateLv1');//....普通办证

//Route::get('/ship', function (\Illuminate\Http\Request $request) {
////    dd($request->getHost());
//    $id = $request->input('id');
////    event(new OrderShipped($id)); // 触发事件
//    event(new \App\Events\OrderShipped($id));
//    return \Illuminate\Support\Facades\Response::make('Order Shipped!');
////    return Response::make('Order Shipped!');
//});
