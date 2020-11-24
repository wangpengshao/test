<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * 借阅宝电子证
 * Class JybService
 * @package App\Services
 */
class JybService
{
    /**
     * @var string
     */
    protected $appid = 'wxapp2';
    /**
     * @var string
     */
    protected $secret = '0b8c05acd4164bfab96ac883c8facdc0';
    /**
     * @var string
     */
    protected $clientId = 'wxclient2';
    /**
     * @var string
     */
    protected $libcode = 'wechat';
    /**
     * @var string
     */
    protected $tokenCacheKey = 'JYB_wechat1_token';
    /**
     * @var string
     */
    protected $host = 'https://alipay.dataesb.com/api';
    /**
     * @var
     */
    protected $token;

    /**
     * 获取授权token
     * @param bool $refresh
     * @return bool|mixed
     */
    protected function getToken($refresh = false)
    {
        if (Cache::has($this->tokenCacheKey) && $refresh != true) {
            return Cache::get($this->tokenCacheKey);
        }
        $sss = substr(microtime(), 2, 3);
        $timestamp = date('YmdHis') . $sss;

        $http = new Client();
        $url = $this->host . '/qrcode/token?';
        $sign = md5($this->appid . $this->clientId . $this->libcode . $timestamp . $this->secret);

        $params = http_build_query([
            'appid' => $this->appid,
            'clientId' => $this->clientId,
            'timestamp' => $timestamp,
            'libcode' => $this->libcode,
            'sign' => $sign,
        ]);
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        Cache::forget($this->tokenCacheKey);
        if ($response['code'] == 200) {
            Cache::put($this->tokenCacheKey, $response['token'], 15);
            return $response['token'];
        }
        return false;
    }

    /**
     * 获取电子二维码
     * @param $wxuser
     * @param $rdid
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getElectronicCard($wxuser, $rdid)
    {
        //非开发环境默认返回
        if (config('app.env') != 'production') {
            return ['code' => 200, 'uuid' => 'JYB12345678910'];
        }
        $this->token = $this->getToken();
        $response = $this->requestElectronicCard($wxuser, $rdid);
        if ($response['code'] == '100001') {
            $this->token = $this->getToken(true);  //token无效重新获取,重试
            $response = $this->requestElectronicCard($wxuser, $rdid);
        }
        return $response;
    }

    /**
     * @param $wxuser
     * @param $rdid
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function requestElectronicCard($wxuser, $rdid)
    {
        $sss = substr(microtime(), 2, 3);
        $timestamp = date('YmdHis') . $sss;
        $sign = md5($this->appid . $this->clientId . $wxuser['libcode'] . $timestamp . $wxuser['glc'] . $rdid . $this->token);
        $url = $this->host . '/qrcode/client/user?';
        $params = http_build_query([
            'appid' => $this->appid,
            'clientId' => $this->clientId,
            'timestamp' => $timestamp,
            'rdid' => $rdid,
            'libcode' => $wxuser['libcode'],
            'sign' => $sign,
            'glc' => $wxuser['glc']
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        return $response;
    }

    /**
     * 电子证二维码填充读者证
     * @param      $wxuser
     * @param      $reader
     * @param      $uuid
     * @param bool $print
     * @return array|mixed|\Psr\Http\Message\ResponseInterface|string
     */
    public function saveSerial($wxuser, $reader, $uuid, $print = false)
    {
        if (config('app.env') != 'production') {
            return ['code' => 200];
        }
        $this->token = $this->getToken();
        $response = $this->requestSerial($wxuser, $reader, $uuid, $print);
        if ($print) {
            return $response;
        }
        if ($response['code'] == '100001') {
            $this->token = $this->getToken(true);            //token无效重新获取,重试
            $response = $this->requestSerial($wxuser, $reader, $uuid);
        }
        return $response;
    }

    /**
     * @param      $wxuser
     * @param      $reader
     * @param      $uuid
     * @param bool $print
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     */
    protected function requestSerial($wxuser, $reader, $uuid, $print = false)
    {
        $sss = substr(microtime(), 2, 3);
        $timestamp = date('YmdHis') . $sss;
        $rdid = $reader['rdid'];
//        md5(appid + clientId + libcode + timestamp + glc + rdid + uuid + token)
        $sign = md5($this->appid . $this->clientId . $wxuser['libcode'] . $timestamp . $wxuser['glc'] . $rdid . $uuid . $this->token);
        $url = $this->host . '/qrcode/client/set_user?';
        $params = http_build_query([
            'appid' => $this->appid,
            'clientId' => $this->clientId,
            'timestamp' => $timestamp,
            'libcode' => $wxuser['libcode'],
            'sign' => $sign,
            'glc' => $wxuser['glc'],
            'token' => $this->token,
            'rdid' => $rdid,
            'uuid' => $uuid,
        ]);
        if ($print) {
            return $url . $params;
        }
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        return $response;
    }

}
