<?php


namespace App\Http\Controllers\Api\Mini;

use App\Http\Controllers\Controller;
use App\Jobs\MiniBzNotice;
use App\Models\Mini\CertificateLog;
use App\Models\Mini\CertificateOrders;
use App\Models\Mini\CertificateRefund;
use App\Models\Mini\MiniPay;
use App\Models\Mini\MiniProgram;
use App\Models\Mini\Registration;
use App\Services\OpenlibService;
use App\Services\PayLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MiniPayController extends Controller
{
    //付款回调
    public function certificateLv1(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $registration = Registration::getCache($token);
        $miniConfig = $registration->only(['openlib_appid', 'openlib_secret', 'openlib_url', 'openlib_opuser']);
        $openlibService = OpenlibService::make($token, $miniConfig);

        $app = MiniPay::initialize($token);
        $response = $app->handlePaidNotify(function ($message, $fail) use ($token, $app, $registration, $payLogService, $openlibService) {
            // 记录回调日志
            $payLogService->payOrder($token, 'MiniCertificate', $message);
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $whereOrder = [
                'mini_token' => $token,
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

                    $certificateLog = CertificateLog::where(['mini_token' => $token, 'order_id' => $message['out_trade_no']])
                        ->first();
                    $newReader = [
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
                    if ($certificateLog['rdid']) $newReader['rdid'] = $certificateLog['rdid'];
                    $newReader += $certificateLog['data'];
                    if (isset($certificateLog['imgData']['personal_img'])) {
                        $storage = Storage::disk('oss');
                        $existsFile = $storage->exists($certificateLog['imgData']['personal_img']);
                        if ($existsFile) {
                            $newReader['baseimg64'] = base64_encode($storage->get($certificateLog['imgData']['personal_img']));  //base64数据
                        }
                    }
                    //增加读者
                    $response = $openlibService->addreader($newReader);
                    $certificateLog->status = 2; //接口异常
                    if ($response['success'] == true) {
                        $rdid = Arr::get($response, 'messagelist.1.rdid');
                        $card = $rdid ? $rdid : $certificateLog['rdcertify'];

                        $certificateLog->rdid = $card;
                        $certificateLog->status = 1;

                        //发送模板消息
                        if ($registration['template_bz']) {
                            dispatch(new MiniBzNotice($certificateLog))->delay(now()->addSeconds(10))->onQueue('disposable');
                        }

                        //自动绑定证号
                        $where = [
                            'token' => $token,
                            'uid' => $certificateLog['openid'],
                        ];
                        $exists = DB::table('mini_registration_u')->where($where)->exists();
                        if (!$exists) {
                            $where['card'] = $card;
                            $where['created_at'] = date('Y-m-d H:i:s');
                            DB::table('mini_registration_u')->insert($where);
                        }

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

    //退款回调
    public function certificate(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $app = MiniPay::initialize($token);

        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) use ($token, $payLogService) {
            $payLogService->refund($token, 'MiniCertificate', $reqInfo);

            $certificateRefund = CertificateRefund::where('out_refund_no', $reqInfo['out_refund_no'])->first();

            if (!$certificateRefund || $certificateRefund['refund_id'] || $certificateRefund['status'] == 1) {
                return true;
            }
            if ($message['return_code'] === 'SUCCESS') {
                if ($reqInfo['refund_status'] === 'SUCCESS') {
                    $certificateRefund->status = 1;
                    $certificateRefund->refund_str = $reqInfo['refund_recv_accout'];
                    $certificateRefund->refund_id = $reqInfo['refund_id'];
                    $certificateRefund->save();
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }
}
