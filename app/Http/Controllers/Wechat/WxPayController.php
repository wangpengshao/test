<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\Wechat\ArrearsLog;
use App\Models\Wechat\ArrearsOrders;
use App\Models\Wechat\CertificateLog;
use App\Models\Wechat\CertificateOrders;
use App\Models\Wechat\DfArrearsLog;
use App\Models\Wechat\DfArrearsOrders;
use App\Models\Wechat\PrepaidLog;
use App\Models\Wechat\PrepaidOrders;
use App\Models\Wechat\WechatPay;
use App\Services\OpenlibService;
use App\Services\PayLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class WeChatController
 *
 * @package App\Http\Controllers\Wechat
 */
class WxPayController extends Controller
{

    public function certificateLv1(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $openlibService = OpenlibService::make($token);
        $app = WechatPay::initialize($token);
        $response = $app->handlePaidNotify(function ($message, $fail) use ($token, $app, $payLogService, $openlibService) {
            // 记录回调日志
            $payLogService->payOrder($token, 'Certificate', $message);
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $whereOrder = [
                'token' => $token,
                'order_id' => $message['out_trade_no']
            ];
            $order = CertificateOrders::where($whereOrder)->first();
            // 如果订单不存在 或者 订单已经支付过了
            if (!$order || $order['pay_at'] || $order['pay_status'] == 1) {
                return true;
            }
            //官方订单查询支付状态
            $checkPay = $app->order->queryByOutTradeNumber($message['out_trade_no']);
            if ($checkPay['trade_state'] != 'SUCCESS') {
                return true;
            }
            // return_code 表示通信状态，不代表支付状态
            if ($message['return_code'] === 'SUCCESS') {
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    //先更新订单状态
                    $order->transaction_id = $message['transaction_id'];
                    $order->cash_fee = $message['cash_fee'] / 100;
                    $order->pay_at = date('Y-m-d H:i:s', strtotime($message['time_end']));
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

                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    // 用户支付失败
                    $order->pay_status = -1;
                    $order->save();
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }

    public function certificateLv2(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $openlibService = OpenlibService::make($token);
        $app = WechatPay::initialize($token);
        $response = $app->handlePaidNotify(function ($message, $fail) use ($token, $app, $payLogService, $openlibService) {
            // 记录回调日志
            $payLogService->payOrder($token, 'Certificate', $message);
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $whereOrder = [
                'token' => $token,
                'order_id' => $message['out_trade_no']
            ];
            $order = CertificateOrders::where($whereOrder)->first();
            // 如果订单不存在 或者 订单已经支付过了
            if (!$order || $order['pay_at'] || $order['pay_status'] == 1) {
                return true;
            }
            //官方订单查询支付状态
            $checkPay = $app->order->queryByOutTradeNumber($message['out_trade_no']);
            if ($checkPay['trade_state'] != 'SUCCESS') {
                return true;
            }
            // return_code 表示通信状态，不代表支付状态
            if ($message['return_code'] === 'SUCCESS') {
                // 用户是否支付成功
                if (Arr::get($message, 'result_code') === 'SUCCESS') {
                    //先更新订单状态
                    $order->transaction_id = $message['transaction_id'];
                    $order->cash_fee = $message['cash_fee'] / 100;
                    $order->pay_at = date('Y-m-d H:i:s', strtotime($message['time_end']));
                    $order->pay_status = 1;
                    $order->save();

                    $certificateLog = CertificateLog::where(['token' => $token, 'order_id' => $message['out_trade_no']])
                        ->first();
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
                    //办证进行图片上传
                    $peopleInfo = DB::table('w_people_idcard')->where('idCard', $newReader['rdcertify'])->first();
                    if ($peopleInfo && $peopleInfo->personal_img) {
                        $storage = Storage::disk('oss');
                        $existsFile = $storage->exists($peopleInfo->personal_img);
                        if ($existsFile) {
                            $newReader['baseimg64'] = base64_encode($storage->get($peopleInfo->personal_img));  //base64数据
                        }
                    }

                    $newReader += $certificateLog['data'];
                    //增加读者
                    $response = $openlibService->addreader($newReader);
                    $certificateLog->status = 2; //接口异常
                    if ($response['success'] == true) {
                        $rdid = Arr::get($response, 'messagelist.1.rdid');
                        if ($rdid) {
                            $certificateLog->rdid = $rdid;
                        }
                        $certificateLog->status = 1;
                    }
                    $certificateLog->save();

                } elseif (Arr::get($message, 'result_code') === 'FAIL') {
                    // 用户支付失败
                    $order->pay_status = -1;
                    $order->save();
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }

    public function payArrears(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $openlibService = OpenlibService::make($token);
        $app = WechatPay::initialize($token);
        $response = $app->handlePaidNotify(function ($message, $fail) use ($token, $app, $payLogService, $openlibService) {
            // 记录回调日志
            $payLogService->payOrder($token, 'payArrears', $message);
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $whereOrder = [
                'token' => $token,
                'order_id' => $message['out_trade_no']
            ];
            $order = ArrearsOrders::where($whereOrder)->first();
            // 如果订单不存在 或者 订单已经支付过了
            if (!$order || $order['pay_at'] || $order['pay_status'] == 1) {
                return true;
            }
//            官方订单查询支付状态
            $checkPay = $app->order->queryByOutTradeNumber($message['out_trade_no']);
            if ($checkPay['trade_state'] != 'SUCCESS') {
                return true;
            }

            // return_code 表示通信状态，不代表支付状态
            if ($message['return_code'] === 'SUCCESS') {
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    //先更新订单状态
                    $order->transaction_id = $message['transaction_id'];
                    $order->cash_fee = $message['cash_fee'] / 100;
                    $order->pay_at = date('Y-m-d H:i:s', strtotime($message['time_end']));
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

                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    // 用户支付失败
                    $order->pay_status = -1;
                    $order->save();
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }

    public function payDfArrears(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $app = WechatPay::initialize($token);
        $openlibService = OpenlibService::make($token);

        $response = $app->handlePaidNotify(function ($message, $fail) use ($token, $app, $payLogService, $openlibService) {
            // 记录回调日志
            $payLogService->payOrder($token, 'dfArrears', $message);
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $whereOrder = [
                'token' => $token,
                'order_id' => $message['out_trade_no']
            ];
            $order = DfArrearsOrders::where($whereOrder)->first();
            // 如果订单不存在 或者 订单已经支付过了
            if (!$order || $order['pay_at'] || $order['pay_status'] == 1) {
                return true;
            }
//            官方订单查询支付状态
            $checkPay = $app->order->queryByOutTradeNumber($message['out_trade_no']);
            if ($checkPay['trade_state'] != 'SUCCESS') {
                return true;
            }

            // return_code 表示通信状态，不代表支付状态
            if ($message['return_code'] === 'SUCCESS') {
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    //先更新订单状态
                    $order->transaction_id = $message['transaction_id'];
                    $order->cash_fee = $message['cash_fee'] / 100;
                    $order->pay_at = date('Y-m-d H:i:s', strtotime($message['time_end']));
                    $order->pay_status = 1;
                    $order->save();

                    $prepaidLog = DfArrearsLog::where(['token' => $token, 'order_id' => $message['out_trade_no']])->get();
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

                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    // 用户支付失败
                    $order->pay_status = -1;
                    $order->save();
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }


}
