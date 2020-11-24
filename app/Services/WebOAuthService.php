<?php

namespace App\Services;

use App\Models\Wechat\Fans;
use App\Models\Wechat\Wechatapp;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebOAuthService
{
    private $request;
    private $token;
    private $app;

    public function __construct(Request $request, Wechatapp $wechatapp)
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
        $this->token = $request->route('token') ?: $request->input('token');
        if (empty($this->token)) {
            abort(404);
        }
        $this->app = $wechatapp->initialize($this->token);
        $this->request = $request;
    }

    public function checkOauth()
    {
        $sessionKey = 'fansInfo:' . $this->token;

        if (!$this->request->session()->has($sessionKey)) {

            if ($this->request->filled(['code', 'state'])) {
                $response = $this->webCodeGetFansInfo($this->request->input('code'));
                if (array_get($response, 'errcode')) {
                    $this->jumpAuth();
                }
                //****** 增加 || 更新 fans数据
                $user = array_only($response, config('fansParams'));
                $user['token'] = $this->token;
                Fans::updateOrCreate(['token' => $user['token'], 'openid' => $user['openid']], $user);

                $this->request->session()->put($sessionKey, $response);
                $this->request->session()->save();
                return $response;
            }
            $this->jumpAuth();
        }
        return $this->request->session()->get($sessionKey);
    }


    public function webCodeGetFansInfo($code)
    {
        $config = $this->app->config;
        $http = new Client();
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $params = http_build_query([
            'appid' => $config['app_id'],
            'secret' => $config['secret'],
            'code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);

        if (array_get($response, 'errcode')) {
            return $response;
        }
        $cacheKey = 'webAccessToken_' . $this->token . '_' . $response['openid'];
        Cache::put($cacheKey, $response, Carbon::now()->addSecond($response['expires_in'] - 200));

        $url = 'https://api.weixin.qq.com/sns/userinfo?';
        $params = http_build_query([
            'access_token' => $response['access_token'],
            'openid' => $response['openid'],
            'lang' => 'zh_CN'
        ]);
        $response = $http->get($url . $params);
        return json_decode((string)$response->getBody(), true);
    }


    public function jumpAuth()
    {
        $config = $this->app->config;
        $current = url()->current();
        $params = $this->request->except(['code', 'state']);
        if ($params) {
            $current .= '?' . http_build_query($params);
        }
        $url = 'https://b.dataesb.com/get-wechat-code.html?';
        $params = http_build_query([
            'appid' => $config['app_id'],
            'state' => 'uWei',
            'redirect_uri' => $current,
            'scope' => 'snsapi_userinfo',
        ]);
//        header("Location: " . $url . $params);
        $this->commonRedirect($url . $params);
        exit();
    }

    public function checkSubscribe($openId)
    {
        $user = $this->app->user->get($openId);
        $subscribe = array_get($user, 'subscribe');
        return $subscribe;
    }

    protected function commonRedirect($url)
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit();
        } else {
            $str = "<meta http-equiv='Refresh' content='0;URL={$url}'>";
            exit($str);
        }
    }


}
