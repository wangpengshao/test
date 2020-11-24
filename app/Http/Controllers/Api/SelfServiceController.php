<?php

namespace App\Http\Controllers\Api;

use App\Services\JybService;
use Illuminate\Http\Request;

class SelfServiceController extends BaseController
{
    public function scanQrCodeLogin(Request $request, JybService $jybService)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $uuid = $request->route('id');
        $wxuser = $this->getWxuserCache($request->user()->token);
        $response = $jybService->saveSerial($wxuser, $reader, $uuid);

        if (array_get($response, 'code') === '200') {
            return $this->success('扫码登录成功!');
        }

        if (array_get($response, 'code') === '200001') {
            return $this->failed('二维码无效,请刷新机器的二维码并点击下方返回按钮重新进行扫码!', 400);
        }

        if (array_get($response, 'code') === '200003') {
            return $this->failed('当前的馆与本人读者证所在馆不匹配,无法登录!', 400);
        }
        return $this->failed('系统繁忙,请稍后再试!', 400);
    }

}
