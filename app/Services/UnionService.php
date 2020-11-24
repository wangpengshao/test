<?php

namespace App\Services;

use App\Models\Wechat\OtherConfig;
use App\Models\Wxuser;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

/**
 * Class UnionService
 * @package App\Services
 */
class UnionService
{
    /**
     * @var array
     */
    protected static $instance = array();
    /**
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $config; //['appid','secret','url','opuser']
    /**
     * @var
     */
    protected $client;

    /**
     * @var string
     */
    protected $appid = 'weixin';

    /**
     * @var string
     */
    protected $secret = '6b5bf263d766d5d5603817ead799f382';

    /**
     * @param string $token
     * @param array  $config
     * @return $this
     */
    public static function make(string $token, $config = [])
    {
        if (isset(self::$instance[$token])) {
            return self::$instance[$token];
        }
        self::$instance[$token] = new static();
        return self::$instance[$token]->setConfig($token, $config);
    }

    /**
     * @param       $path
     * @param array $params
     * @return string
     */
    protected function autoHttpUrl($path, array $params = array())
    {
        return Str::finish($this->getConfig('url'), '/') . $path . '?' . http_build_query($params);
    }

    /**
     * 封装请求方法
     * @param string $method
     * @param        $url
     * @param array  $form_params
     * @return array|mixed
     * @throws
     */
    protected function sendRequest($method = 'GET', $url, array $form_params = array())
    {
        if (empty($this->client)) {
            $this->client = new Client();
        }
        $basis = [
            'timeout' => 10.0,
            'connect_timeout' => 15.0,
            'http_errors' => true
        ];
        //POST表单请求
        if (count($form_params) > 0 && strtoupper($method) === 'POST') {
            $basis['form_params'] = $form_params;
        }

        try {
            $response = $this->client->request($method, $url, $basis);
        } catch (RequestException $e) {
            $context = [
                'url' => $url,
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ];
            if ($e->hasResponse()) {
                $context['mes'] = $e->getResponse()->getReasonPhrase();
            }
            $logger = new Logger('HTTP');
            $logger->pushHandler(new RotatingFileHandler(storage_path('logs/union.log')));
            $logger->pushProcessor(new WebProcessor(null, ['ip']));
            $logger->error($this->token, $context);

            $response = [
                'messagelist' => [['message' => 'union接口异常,请稍后再试', 'code' => 10000]],
                'success' => false
            ];
            return $response;
        }
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param string $method
     * @param        $url
     * @param        $params
     * @param array  $postData
     * @return array|mixed
     */
    protected function httpMagic($method = 'GET', $url, $params, array $postData = [])
    {
        $response = $this->getToken();
        if ($response['success'] == false) {
            return $response;
        }
        $params['token'] = Arr::get($response, 'messagelist.0.token');
        return $this->sendRequest($method, $this->autoHttpUrl($url, $params), $postData);
    }

    /**
     * @param $token
     * @param $config
     * @return $this
     */
    public function setConfig($token, $config)
    {
        $this->token = $token;
        if ($config && ($config instanceof Model || is_array($config))) {
            $this->config = [
                'appid' => $this->appid,
                'secret' => $this->secret,
                'url' => $config['union_url'],
                'opuser' => ''
            ];
        }
        return $this;
    }

    /**
     * @param $need
     * @return array|mixed
     */
    public function getConfig($need)
    {
        if (empty($this->config)) {
            $wxuser = Wxuser::getCache($this->token);
            $unionUrl = OtherConfig::union($wxuser['id'])->first();
            if (empty($wxuser)) abort(404);
            $this->config = [
                'appid' => $this->appid,
                'secret' => $this->secret,
                'url' => $unionUrl['union_url'],
                'opuser' => ''
            ];
        }
        if (is_array($need)) {
            return Arr::only($this->config, $need);
        }
        if (is_string($need)) {
            return Arr::get($this->config, $need);
        }
        return $this->config;
    }

    /**
     * @param bool $refresh
     * @return array|mixed
     */
    public function getToken($refresh = false)
    {
        $cacheKey = 'union:' . $this->token;
        if ($refresh == true) {
            Cache::forget($cacheKey);
        }
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $params = $this->getConfig(['appid', 'secret']);
            $url = $this->autoHttpUrl('service/token', $params);
            $response = $this->sendRequest('GET', $url);
            if ($response['success'] == false) {
                return $response;
            }
            $time = Carbon::parse(Arr::get($response, 'messagelist.0.time'))->subHour(1);
            Cache::put($cacheKey, $response, $time);
            return $response;
        }
        return $cache;
    }


    public function confirmclusreader($rdid, $rdpasswd, $libcode)
    {
        /**
         * libcode    分馆代码
         * rdid   读者证号
         * rdpasswd      读者所在集群馆唯一编码 havecluster 为1时，必填
         */
        $params = [
            'rdpasswd' => $rdpasswd,
            'rdid' => $rdid,
            'libcode' => $libcode
        ];
        return $this->httpMagic('GET', 'service/reader/confirmclusreader', $params);
    }


}
