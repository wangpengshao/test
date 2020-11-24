<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Search\Search;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Api\Helpers\ApiResponse;

/**
 * Class SearchController
 * @package App\Http\Controllers\PublicApi
 */
class SearchController extends Controller
{
    use  ApiResponse;

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = request()->all();
        if (!$request->Filled('sign', 'time')) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        //验签
        if ($data['sign'] != md5($data['time'] . 'search')) {
            return $this->message('sign is invalid', false);
        }
        //设置失效时间为300s
        $s = time() - $data['time'];
        if ($s > 300 || $s < -300) {
            return $this->message('link is invalid', false);
        }
        if ($request->anyFilled('wxname', 'token')) {
            //根据wxname去获取部分数据
            if (!empty($data['wxname'])) {
                $searchData = Search::paramSearch($data)
                    ->get(['wxname', 'token', 'opacurl', 'libcode'])
                    ->toArray();
            } else {
                //根据token去获取详细数据
                $searchData = Search::paramSearch($data)
                    ->get()
                    ->toArray();
            }
        } else {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        if (!empty($searchData)) {
            return $this->success($searchData, true);
        } else {
            return $this->message('搜索结果为空!', false);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\HttpException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function wxConf(Request $request)
    {
        if (!$request->Filled('sign', 'time', 'token', 'type')) {
            return $this->message('lack of parameter', false);
        }
        $sign = $request->input('sign');
        $time = $request->input('time');
        $token = $request->input('token');
        $type = $request->input('type');
        //验签
        if ($sign != md5($time . 'oldUwei2020')) {
            return $this->message('sign is invalid', false);
        }

        if ($type == 'conf') {
            $wxuser = Wxuser::getCache($token);
            if (!$wxuser) {
                return $this->message('token is invalid', false);
            }
            $wxConf = [
                'appid' => $wxuser['appid'],
                'appsecret' => $wxuser['appsecret']
            ];
            return $this->success($wxConf, true);
        }

        if ($type == 'access_token') {
            $accessToken = Wechatapp::initialize($token)->access_token->getToken();
            return $this->success($accessToken, true);
        }

        return $this->failed('非法请求', 400);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function fansInfo(Request $request)
    {
        if (!$request->filled(['type', 'token', 'code'])) {
            return $this->message('缺少必填参数', false);
        }
        $type = $request->input('type');
        $token = $request->input('token');
        $code = $request->input('code');

        if ($type !== 'openid' && $type !== 'info') {
            return $this->message('type is invalid', false);
        }
        $config = Wxuser::getConfig($token);
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $params = http_build_query([
            'appid' => $config['app_id'],
            'secret' => $config['secret'],
            'code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        if (isset($response['errcode'])) {
            return $this->message($response['errmsg'], false);
        }
        if ($type === 'openid') {
            return $this->success($response, true);
        }
        $url = 'https://api.weixin.qq.com/sns/userinfo?';
        $params = http_build_query([
            'access_token' => $response['access_token'],
            'openid' => $response['openid'],
            'lang' => 'zh_CN'
        ]);
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        if (isset($response['errcode'])) {
            return $this->message($response['errmsg'], false);
        }
        return $this->success($response, true);
    }

}
