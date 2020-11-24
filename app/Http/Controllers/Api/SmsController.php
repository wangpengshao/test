<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    use ApiResponse;

    public function vueSend(Request $request)
    {
//        $token = $request->user()->token;
        $token = $request->input('token');
        $phone = $request->input('phone');
        $time = $request->input('time');
        if (empty($phone)) {
            return $this->failed('缺少必填参数!', 400);
        }
        if (md5($phone . $time . config('envCommon.ENCRYPT_STR')) != $request->input('sign') || time() - $time > 120) {
            return $this->failed('Signature error.', 400);
        }
        $Sms = new SmsService($token, $phone);
        $sendStatus = $Sms->sendVerifyCode();
        if ($sendStatus['status']) {
            return $this->message('验证码发送成功', true);
        }
        return $this->message('验证码发送失败,请稍后再试!', false);
    }

    public function vueCheck(Request $request)
    {
//        $token = $request->user()->token;
        $token = $request->input('token');
        $phone = $request->input('phone');
        $code = $request->input('code');
        if (empty($phone) || empty($code)) {
            return $this->failed('缺少必填参数!', 400);
        }
        $Sms = new SmsService($token, $phone);
        $checkStatus = $Sms->checkVerifyCode($code);
        if ($checkStatus) {
            return $this->message('验证成功', true);
        }
        return $this->message('验证失败', false);
    }

}
