<?php

namespace App\Http\Controllers\Api\Main;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Fans;
use App\Services\WechatOAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * api 授权主要接口
 * Class AuthorizeController
 * @package App\Http\Controllers\Api\Main
 */
class AuthorizeController extends Controller
{
    // 通用api辅助函数
    use ApiResponse;

    /**
     * vue前端  openid 授权
     * @param Request $request
     * @return mixed
     */
    public function vueFansOpenid(Request $request)
    {
        if (!$request->filled(['sign', 'openid', 'time', 'token'])) {
            return $this->failed('lack of parameter.', 400);
        }
        $sign = $request->input('sign');
        $openid = $request->input('openid');
        $time = $request->input('time');
        $token = $request->input('token');

        /* md5 openid + time + keyStr */
        if (md5($openid . $time . config('envCommon.ENCRYPT_STR')) != $sign) {
            return $this->failed('sign is invalid.', 400);
        }
        $where = [
            'token' => $token,
            'openid' => $openid
        ];
        $fans = Fans::where($where)->first();
        if (empty($fans)) {
            return $this->failed('openid is invalid.', 400);
        }
        $accessToken = $fans->createToken('openid')->accessToken;
        $success = [
            'token_type' => 'Bearer',
            'expires_in' => (int)config('envCommon.ACCESSTOKEN_SECOND'),
            'access_token' => $accessToken,
            'refresh_token' => ''
        ];
        return $this->success($success, true);
    }

    /**
     * vue前端 微信网页授权code 授权
     * @param Request $request
     * @return mixed
     */
    public function vueFansCode(Request $request)
    {
        if (!$request->filled(['token', 'code'])) {
            return $this->failed('lack of parameter', 400);
        }
        $token = $request->input('token');
        $code = $request->input('code');

        $response = WechatOAuth::make($token)->codeAuth($code);

        if (Arr::has($response, 'errcode')) {
            return $this->failed($response['errmsg'], 400);
        }
        //缓存  access_token (个人对应个人信息)   有效  openid 为必带但是非准确
        $fansInfo = Arr::only($response, config('fansParams'));
        $fansInfo['token'] = $token;

        $fans = Fans::updateOrCreate(['token' => $fansInfo['token'], 'openid' => $fansInfo['openid']], $fansInfo);

        $accessToken = $fans->createToken('openid')->accessToken;

        $success = [
            'token_type' => 'Bearer',
            'expires_in' => (int)config('envCommon.ACCESSTOKEN_SECOND'),
            'access_token' => $accessToken,
            'refresh_token' => '',
            'wechatinfo' => $fansInfo
        ];
        return $this->success($success, true);
    }

}
