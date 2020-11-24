<?php

namespace App\Http\Controllers\Wechat;

use App\Api\Helpers\ApiResponse;
use App\Models\Wechat\Fans;
use App\Models\Wechat\Wechatapp;
use App\Services\WebOAuthService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

/**
 * Class WeChatController
 *
 * @package App\Http\Controllers\Wechat
 */
class FansController
{

    use ApiResponse;

    public function fansAuthorization(Request $request, WebOAuthService $webOAuthService)
    {
        if (!$request->filled(['token', 'code'])) return $this->failed('lack of parameter', 400);

        $token = $request->input('token');
        $response = $webOAuthService->webCodeGetFansInfo($request->input('code'));
        if (array_get($response, 'errcode')) {
            return $this->failed(array_get($response, 'errmsg'), 400);
        }
        //缓存  access_token (个人对应个人信息)   有效  openid 为必带但是非准确
        $user = array_only($response, config('fansParams'));
        $user['token'] = $token;
        Fans::updateOrCreate(['token' => $user['token'], 'openid' => $user['openid']], $user);

        //内部授权
        $authDATA = [
            'username' => $user['openid'],
            'password' => md5($user['openid']),
        ];
        $http = new Client();
        $response = $http->post($request->root() . '/oauth/token', [
            'form_params' => config('Fanspasspost') + $authDATA
        ]);
        $response = json_decode((string)$response->getBody(), true) + ['wechatinfo' => $user];
        return $this->success($response, true);
    }


    public function getFansInfo(Request $request)
    {
        if (!$request->filled('openid')) return $this->failed('lack of parameter', 400);
        $token = $request->input('token');
        $openid = $request->input('openid');
        $app = Wechatapp::initialize($token);
        $response = $app->user->get($openid);

        if (array_get($response, 'errcode')) {
            return $this->failed(array_get($response, 'errmsg'), 400);
        }

        if (array_get($response, 'subscribe') === 0) {
            $response = Fans::where(['token' => $token, 'openid' => $openid])->first(config('fansParams'))->toArray();
            $response['subscribe'] = 0;
        }
        return $this->success($response, true);
    }

}
