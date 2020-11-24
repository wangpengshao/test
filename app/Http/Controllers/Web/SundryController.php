<?php

namespace App\Http\Controllers\Web;

use App\Models\Wechat\Wechatapp;
use App\Services\WechatOAuth;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SundryController extends Controller
{
    public function __construct(Request $request)
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
        $this->middleware('RequiredToken');
    }

    public function btnText(Request $request)
    {
        $token = $request->input('token');
        $message = urldecode($request->input('text'));

        $app = WechatOAuth::make($token);
        $userInfo = $app->webOAuth($request);
        $res = $this->sendText(Wechatapp::initialize($token), $userInfo['openid'], $message);
        echo "<script>setInterval(function(){WeixinJSBridge.call('closeWindow');},100)</script>";
        exit;
    }

    protected function sendText($app, $openid, $text)
    {
        $message = new Text($text);
        $result = $app->customer_service->message($message)->to($openid)->send();

        return $result;
    }

}
