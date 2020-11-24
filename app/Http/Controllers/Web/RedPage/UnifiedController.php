<?php

namespace App\Http\Controllers\Web\RedPage;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\RedPageLog;
use App\Models\Wechat\Wechatapp;
use App\Models\Wechat\WechatPay;
use App\Services\PayLogService;
use App\Services\SmsService;
use App\Services\WebOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Class TestController
 * @package App\Http\Controllers\Web
 */
class UnifiedController extends Controller
{
    use ApiResponse;

    public function index(Request $request, WebOAuthService $webOAuthService, PayLogService $payLogService)
    {
        dd('来晚了，已停止领取');
//        $code = $request->route('wmCode');
//        $oldOpenid = $request->input('oldOpenid');
//        $fansInfo = $webOAuthService->checkOauth();
//        if ($request->isMethod('post')) {
//            $phone = $request->input('phone');
//            $phoneCode = $request->input('phoneCode');
//            $token = '542ef3edc367';
//            if (!$code || !$token || !$oldOpenid || $token != $request->input('token') || !$phone || !$phoneCode) {
//                return $this->message('非法访问', false);
//            }
////            $checkData = Cache::pull('checkOpenid:' . $code);
////            $redPageData = Cache::pull('redPage:' . $checkData['token'] . ':' . $oldOpenid);
//            $checkData = Cache::get('checkOpenid:' . $code);
//            $redPageData = Cache::get('redPage:' . $checkData['token'] . ':' . $oldOpenid);
//            if (!$checkData || !$redPageData || $checkData['openid'] != $oldOpenid || !$checkData['table']) {
//                return $this->message('非法访问', false);
//            }
//            //检查手机验证码
//            $sms = new SmsService($checkData['token'], $phone);
//            if (!$sms->checkVerifyCode($phoneCode)) {
//                return $this->message('验证码错误', false);
//            }
//            //一个手机号码对应一个活动只能领取一次
//            $phoneCheck = [
//                'token' => $checkData['token'],
//                'a_id' => $checkData['id'],
//                'phone' => $phone,
//                'table' => $checkData['table']
//            ];
//            $exists = DB::table('w_redpage_phone_keep')->where($phoneCheck)->exists();
//            if ($exists) return $this->message('抱歉，您的手机已经领取过奖励了', false);
//
//            $first = DB::table($checkData['table'])->where([
//                'id' => $checkData['id'],
//                'status' => 0
//            ])->first();
//            if (empty($first)) return $this->message('非法访问', false);
//
//            if ($redPageData) {
//                $redPageData['re_openid'] = $fansInfo['openid'];
//            }
//            $payment = WechatPay::initialize($token);
//            $redpack = $payment->redpack;
//            $result = $redpack->sendNormal($redPageData);
//
//            $payLogService->redPackAgent($checkData['token'], 'agent', $result);
//
//            $status = false;
//            if ($result['return_code'] === 'SUCCESS') {
//                $redPageLog = RedPageLog::create($result);
//                if (array_get($result, 'result_code') === 'SUCCESS') {
//                    Cache::forget('checkOpenid:' . $code);
//                    Cache::forget('redPage:' . $checkData['token'] . ':' . $oldOpenid);
//                    $status = true;
//                    $message = '红包发送成功,请注意查收';
//                    DB::table('w_redpage_phone_keep')->insert($phoneCheck);
//                    //更新奖品领取状态
//                    DB::table($checkData['table'])->where('id', $first->id)->update([
//                        'redpack_log' => $redPageLog['id'],
//                        'status' => 1
//                    ]);
//                    //更新红包数据状态
//                    DB::table($checkData['pageTable'])->where('id', $first->redpack_id)->update([
//                        'isValid' => 0,
//                        'status' => 1,
//                        'update_at' => date('Y-m-d H:i:s')
//                    ]);
//                } else {
//                    //展示失败原因
//                    $message = $result['err_code_des'];
//                }
//            } else {
//                $message = $result['return_msg'];
//            }
//            if (!$status) {
//                return $this->message($message, false);
//            }
//            return $this->success(['message' => $message, 'redirect' => $checkData['redirect']], $status);
//
//        } else {
//            $checkData = Cache::get('checkOpenid:' . $code);
//            $redPageData = Cache::get('redPage:' . $checkData['token'] . ':' . $oldOpenid);
//            if (empty($checkData) || empty($redPageData)) {
//                return '当前页面已失效';
//            }
//            return view('web.redPage.check', [
//                'sendMesUrl' => route('RedPage::sendMes', ['token' => $checkData['token']]),
//                'fansInfo' => ['openid' => '123123'],
//            ]);
//        }

//        $checkData = Cache::pull('checkOpenid:' . $code);
//        $redPageData = Cache::pull('redPage:' . $checkData['token'] . ':' . $oldOpenid);
//        if (!$checkData || !$redPageData || $checkData['openid'] != $oldOpenid || !$checkData['table']) {
//            abort(404);
//        }
//        $first = DB::table($checkData['table'])->where([
//            'id' => $checkData['id'],
//            'status' => 0
//        ])->first();
//        if (empty($first)) abort(404);
//
//        if ($redPageData) {
//            $redPageData['re_openid'] = $fansInfo['openid'];
//        }
//        $payment = WechatPay::initialize($token);
//        $redpack = $payment->redpack;
//        $result = $redpack->sendNormal($redPageData);
//
//        $payLogService->redPackAgent($checkData['token'], 'agent', $result);
//
//        $status = 0;
//        if ($result['return_code'] === 'SUCCESS') {
//            $redPageLog = RedPageLog::create($result);
//            if (array_get($result, 'result_code') === 'SUCCESS') {
//                $status = 1;
//                $message = '红包发送成功,请注意查收';
//                DB::table($checkData['table'])->where('id', $first->id)->update([
//                    'redpack_log' => $redPageLog['id'],
//                    'status' => 1
//                ]);
//            } else {
//                //展示失败原因
//                $message = $result['err_code_des'];
//            }
//        } else {
//            $message = $result['return_msg'];
//        }
//        return view('web.redPage.index', [
//            'app' => Wechatapp::initialize($token),
//            'message' => $message,
//            'status' => $status,
//            'redirect' => $checkData['redirect']
//        ]);
    }

    public function sendMes(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $token = $request->input('token');
        $phone = $request->input('phone');
        $openid = $fansInfo['openid'];
        if (empty($token) || empty($phone) || empty($openid)) {
            return $this->message('非法访问', false);
        }
        $cacheKey = 'sendLimit:' . $openid;
        $sendLimit = Cache::get($cacheKey);
        if (empty($sendLimit)) {
            $Sms = new SmsService('18c6684c', $phone);
            $sendStatus = $Sms->sendVerifyCode();
            if ($sendStatus['status'] == true) {
                $smsLog = [
                    'phone' => $phone,
                    'token' => $token,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                DB::table('w_redpage_smslog')->insert($smsLog);
                Cache::put($cacheKey, 1, 1);
                return $this->message('发送成功!', true);
            }
        }
        return $this->message('请稍后再试!', false);
    }
}
