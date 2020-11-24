<?php

namespace App\Http\Controllers\PublicApi;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Wechatapp;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    use ApiResponse;

    public function getAccessToken(Request $request)
    {
        $token = $request->input('token');
        $app = Wechatapp::initialize($token);
        $accessToken = $app->access_token;
        $token = $accessToken->getToken(true); // token 数组  token['access_token'] 字符串
        return $this->success($token, true);
    }

    public function getJsSdk(Request $request)
    {
        $token = $request->input('token');
        $targetUrl = $request->input('targetUrl');
        $type = $request->input('type', 'jssdk');

        if ($type == 'jsapi') {
            $app = Wechatapp::initialize($token);
            $ticket = $app->jssdk->getTicket();
            return $this->success($ticket, true);
        }

        if (!$targetUrl) return $this->message('lack of parameter', false);

        $isURL = filter_var($targetUrl, FILTER_VALIDATE_URL);
        if (!$isURL) return $this->message('Illegal parameter', false);

        $app = Wechatapp::initialize($token);
        $app->jssdk->setUrl($targetUrl);

        return $this->success($app->jssdk->buildConfig([], $debug = false, $beta = false, $json = false), true);
    }

}
