<?php

namespace App\Services;

use App\Models\Wechat\Fans;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class WechatOAuth
 * @package App\Services
 */
class WechatOAuth
{
    /**
     * @var
     */
    private $config;
    /**
     * @var
     */
    private $token;
    /**
     * @var
     */
    private $oldToken;
    /**
     * @var
     */
    private $http;
    /**
     * @var
     */
    private $request;
    /**
     * @var string
     */
    private $stateKey = 'uWei2020';
    /**
     * @var
     */
    private $redirectUrl;

    /**
     * @param string $token
     * @param array  $customConfig
     * @return WechatOAuth
     */
    public static function make(string $token, array $customConfig = [])
    {
        return (new static())->initConfig($token, $customConfig);
    }

    /**
     * @param $token
     * @param $customConfig
     * @return $this
     */
    private function initConfig($token, $customConfig)
    {
        $this->token = $token;
        $this->http = new Client();
        if ($customConfig) {
            $this->config = $customConfig;
        } else {
            $wxUser = Wxuser::getConfig($token);
            if(empty($wxUser['old_token'])){
                $this->config = $wxUser;
            } else {
                $this->oldToken = $wxUser['old_token'];
                $config = $this->getOldConfig($wxUser['old_token']);
                $this->config['app_id'] = $config['appid'];
                $this->config['secret'] = $config['appsecret'];
            }
        }
        return $this;
    }

    /**
     * @param string $code
     * @return mixed
     */
    private function accessToken(string $code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $params = http_build_query([
            'appid' => $this->config['app_id'],
            'secret' => $this->config['secret'],
            'code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        $response = $this->http->get($url . $params);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param $access_token
     * @return mixed
     */
    private function userInfo($access_token)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?';
        $params = http_build_query([
            'access_token' => $access_token['access_token'],
            'openid' => $access_token['openid'],
            'lang' => 'zh_CN'
        ]);
        $response = $this->http->get($url . $params);
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function codeAuth(string $code)
    {
        $response = $this->accessToken($code);
        if (Arr::has($response, 'errcode')) {
            return $response;
        }
        return $this->userInfo($response);
    }

    /**
     * 网页授权
     * @param Request $request
     * @return mixed
     */
    public function webOAuth(Request $request)
    {
        if ($request->session()->has($this->getUserInfoKey())) {
            return $request->session()->get($this->getUserInfoKey());
        }
        //$this->request = $request;
        $params = $request->except(['code', 'state']);
        $this->redirectUrl = ($params) ? $request->url() . '?' . http_build_query($params) : $request->url();

        $code = $request->input('code');
        $state = $request->input('state');

        $RedirectResponse = new RedirectResponse($this->getAuthUrl());

        if ($state != $this->stateKey || empty($code)) {
            $RedirectResponse->send();
            exit();
        }
        $response = $this->codeAuth($code);
        if (Arr::get($response, 'errcode')) { //无效的code重试
            $RedirectResponse->send();
            exit();
        }
        //****** 增加 || 更新 fans数据
        $user = Arr::only($response, config('fansParams'));
        $user['token'] = $this->token;
        Fans::updateOrCreate(['token' => $user['token'], 'openid' => $user['openid']], $user);

        $request->session()->put($this->getUserInfoKey(), $response);
        return $response;
    }

    /**
     * 获取网页授权跳板地址
     * @return string
     */
    protected function getAuthUrl()
    {
        $url = 'https://b.dataesb.com/get-wechat-code.html?';

        if(!empty($this->oldToken)){
            $url = 'https://u.interlib.cn/newthird/get-wechat-code.html?';
        }

        $params = http_build_query([
            'appid' => $this->config['app_id'],
            'state' => $this->stateKey,
            'redirect_uri' => $this->redirectUrl,
            'scope' => 'snsapi_userinfo',
        ]);
        return $url . $params;
    }

    /**
     * @return string
     */
    protected function getUserInfoKey()
    {
        return 'fansInfo:' . $this->token;
    }

    /**
     * @param $openId
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function checkSub($openId)
    {
        $app = Wechatapp::initialize($this->token);
        $user = $app->user->get($openId);
        return Arr::get($user, 'subscribe');
    }

    /**
     * @param $token
     * @return array
     */
    public function getOldConfig($token)
    {
        $cacheKey = 'wxConfig:' . $token;
        $cache = Cache::get($cacheKey);

        if(empty($cache)){
            $url = 'https://u.interlib.cn/index.php?g=Mysql&m=Apidata&a=getWxConfig&token=' . $token;
            $time = time();
            $params = http_build_query([
                'time' => $time,
                'sign' => md5($time . 'search')
            ]);
            $response = $this->http->get($url . '&' . $params);
            $result =  json_decode((string)$response->getBody(), true);

            if($result['status'] == false){
                abort(404);
            }
            $cache = $result['data'];
            Cache::put($cacheKey, $cache, 60);
        }

        return $cache;
    }

}
