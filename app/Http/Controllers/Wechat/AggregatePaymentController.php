<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Wechat\AggregatePayment;
use App\Models\Wechat\ArrearsLog;
use App\Models\Wechat\ArrearsOrders;
use App\Models\Wechat\CertificateLog;
use App\Models\Wechat\CertificateOrders;
use App\Models\Wechat\WechatPay;
use App\Services\IcbcService;
use App\Services\OpenlibService;
use App\Services\PayHelper;
use App\Services\PayLogService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;

class AggregatePaymentController extends Controller
{
    /**
     * 欠款缴费回调
     * @param Request $request
     * @param PayLogService $payLogService
     * @param PayHelper $payHelper
     * @return bool
     * @throws \Exception
     */
    public function icbcNotify(Request $request, PayLogService $payLogService, PayHelper $payHelper)
    {
        $token = $request->route('token');
        $openlibService = OpenlibService::make($token);
        $icbc = IcbcService::make($token);
        $data = $request->input();
        $biz_content = json_decode($data['biz_content'], true);
        // 记录回调日志
        $payLogService->payOrder($token, 'payArrears', $data);

        $path = parse_url($request->url(),PHP_URL_PATH);
        $strToSign = $icbc->buildSignStr($path, Arr::except($data, array('sign')));
        $passed = $icbc->verify($strToSign, $data['sign'], "RSA");
        if($passed){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $whereOrder = [
                'token' => $token,
                'order_id' => $biz_content['out_trade_no']
            ];
            $order = ArrearsOrders::where($whereOrder)->first();
            // 如果订单不存在 或者 订单已经支付过了
            if (!$order || $order['pay_at'] || $order['pay_status'] == 1) {
                return true;
            }

            //官方订单查询支付状态
            $request = array(
                "method" => 'POST',
                "isNeedEncrypt" => false,
                "biz_content" => array(
                    "order_id" => $biz_content['order_id'],
                )

            );
            $msg_id = $payHelper->GenerateMsgId($token);
            $message = $icbc->execute($request,$msg_id, 'api_payment_query');
            if ($message['return_code'] === '0') {
                // 交易结果标志，0：支付中，1：支付成功， 2：支付中
                if (array_get($message, 'pay_status') === '1') {
                    //先更新订单状态
                    $order->transaction_id = $message['order_id'];
                    $order->cash_fee = $message['total_amt'] / 100;
                    $order->pay_at = date('Y-m-d H:i:s', strtotime($message['pay_time']));
                    $order->pay_status = 1;
                    $order->save();

                    $prepaidLog = ArrearsLog::where(['token' => $token, 'order_id' => $message['out_trade_no']])->get();
                    foreach ($prepaidLog as $k => $v) {
                        $params = [
                            'rdid' => $v['rdid'],
                            'tranid' => $v['tranid'],
                            'optype' => 1,
                            'money' => $v['price'],
                            'moneytype' => 6
                        ];
                        $response = $openlibService->onefinhandle($params);
                        $v->status = 2; //接口异常
                        if ($response['success'] == true) {
                            $v->status = 1;
                        }
                        $v->save();
                    }
                } elseif (array_get($message, 'pay_status') === '2') {
                    // 用户支付失败
                    $order->pay_status = -1;
                    $order->save();
                }
            }
        }

    }

    public function certificateLv1(Request $request, PayLogService $payLogService, PayHelper $payHelper)
    {
        $token = $request->route('token');
        $openlibService = OpenlibService::make($token);
        $icbc = IcbcService::make($token);
        $data = $request->input();
        $biz_content = json_decode($data['biz_content'], true);
        // 记录回调日志
        $payLogService->payOrder($token, 'payArrears', $data);

        $path = parse_url($request->url(),PHP_URL_PATH);
        $strToSign = $icbc->buildSignStr($path, Arr::except($data, array('sign')));
        $passed = $icbc->verify($strToSign, $data['sign'], "RSA");
        if($passed){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $whereOrder = [
                'token' => $token,
                'order_id' => $biz_content['out_trade_no']
            ];
            $order = CertificateOrders::where($whereOrder)->first();
            // 如果订单不存在 或者 订单已经支付过了
            if (!$order || $order['pay_at'] || $order['pay_status'] == 1) {
                return true;
            }

            //官方订单查询支付状态
            $request = array(
                "method" => 'POST',
                "isNeedEncrypt" => false,
                "biz_content" => array(
                    "order_id" => $biz_content['order_id'],
                )

            );
            $msg_id = $payHelper->GenerateMsgId($token);
            $message = $icbc->execute($request,$msg_id, 'api_payment_query');
            if ($message['return_code'] === '0') {
                // 交易结果标志，0：支付中，1：支付成功， 2：支付中
                if (array_get($message, 'pay_status') === '1') {
                    //先更新订单状态
                    $order->transaction_id = $message['order_id'];
                    $order->cash_fee = $message['total_amt'] / 100;
                    $order->pay_at = date('Y-m-d H:i:s', strtotime($message['pay_time']));
                    $order->pay_status = 1;
                    $order->save();

                    $certificateLog = CertificateLog::where(['token' => $token, 'order_id' => $message['out_trade_no']])
                        ->first();
                    if ($certificateLog['check_s'] != -1) {
                        //免审
                        $newReader = [
                            'rdid' => $certificateLog['rdid'],
                            'rdname' => $certificateLog['rdname'],
                            'rdpasswd' => decrypt($certificateLog['rdpasswd']),
                            'rdcertify' => $certificateLog['rdcertify'],
                            'rdlib' => $certificateLog['rdlib'],
                            'operator' => $certificateLog['operator'],
                            'rdtype' => $certificateLog['rdtype'],
                            'deposit' => $message['total_fee'] / 100,
                            'paytype' => 6,
                            'serialno' => $message['transaction_id'],
                            'rdcfstate' => 1
                        ];
                        $newReader += $certificateLog['data'];
                        $response = $openlibService->addreader($newReader);
                        //增加读者
                        $certificateLog->status = 2; //接口异常
                        if ($response['success'] == true) {
                            $rdid = array_get($response, 'messagelist.1.rdid');
                            if ($rdid) {
                                $certificateLog->rdid = $rdid;
                            }
                            $certificateLog->status = 1;
                        }
                        $certificateLog->save();
                    }
                } elseif (array_get($message, 'pay_status') === '2') {
                    // 用户支付失败
                    $order->pay_status = -1;
                    $order->save();
                }
            }
        }
    }

}
