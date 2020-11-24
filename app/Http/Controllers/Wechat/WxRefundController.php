<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\Wechat\ArrearsRefund;
use App\Models\Wechat\CertificateRefund;
use App\Models\Wechat\WechatPay;
use App\Services\PayLogService;
use Illuminate\Http\Request;

/**
 * Class WeChatController
 *
 * @package App\Http\Controllers\Wechat
 */
class WxRefundController extends Controller
{

    public function certificate(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $app = WechatPay::initialize($token);

        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) use ($token, $payLogService) {
            $payLogService->refund($token, 'Certificate', $reqInfo);

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

    public function payArrears(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $app = WechatPay::initialize($token);

        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) use ($token, $payLogService) {
            $payLogService->refund($token, 'PayArrears', $reqInfo);

            $arrearsRefund = ArrearsRefund::where('out_refund_no', $reqInfo['out_refund_no'])->first();

            if (!$arrearsRefund || $arrearsRefund['refund_id'] || $arrearsRefund['status'] == 1) {
                return true;
            }
            if ($message['return_code'] === 'SUCCESS') {
                if ($reqInfo['refund_status'] === 'SUCCESS') {
                    $arrearsRefund->status = 1;
                    $arrearsRefund->refund_str = $reqInfo['refund_recv_accout'];
                    $arrearsRefund->refund_id = $reqInfo['refund_id'];
                    $arrearsRefund->save();
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }

    public function dfArrears(Request $request, PayLogService $payLogService)
    {
        $token = $request->route('token');
        $app = WechatPay::initialize($token);

        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) use ($token, $payLogService) {
            $payLogService->refund($token, 'DfArrears', $reqInfo);

            $arrearsRefund = ArrearsRefund::where('out_refund_no', $reqInfo['out_refund_no'])->first();

            if (!$arrearsRefund || $arrearsRefund['refund_id'] || $arrearsRefund['status'] == 1) {
                return true;
            }
            if ($message['return_code'] === 'SUCCESS') {
                if ($reqInfo['refund_status'] === 'SUCCESS') {
                    $arrearsRefund->status = 1;
                    $arrearsRefund->refund_str = $reqInfo['refund_recv_accout'];
                    $arrearsRefund->refund_id = $reqInfo['refund_id'];
                    $arrearsRefund->save();
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }


}
