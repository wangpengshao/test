<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;


/**
 * Class ActivityApi
 * @package App\Services
 */
class ActivityApi
{

    /**
     * @var
     */
    private $url;


    /**
     * @var bool
     */
    private $existError = false;

    /**
     * @var array
     */
    private $errMes = array('errMes' => 'act接口异常,请稍后再试');

    /**
     * @var
     */
    private $token;

    /**
     * ActivityApi constructor.
     * @param $url
     * @param $token
     */
    public function __construct($url, $token)
    {
        $this->url = $url;
        $this->token = $token;
    }

    /**
     * @param string $url
     * @param string $token
     * @return ActivityApi
     */
    public static function make(string $url, string $token = ''): self
    {
        return new static($url, $token);
    }

    /**
     * @param $path
     * @param $params
     * @return string
     */
    private function getFullUrl($path, $params)
    {
        return $this->url . $path . '?' . http_build_query($params);
    }


    /**
     * @param string $path
     * @param array $params
     * @return array|mixed
     */
    public function request(string $path, array $params)
    {
        $url = $this->getFullUrl($path, $params);
        try {

            $http = new Client();
            $response = $http->get($url, $params);
            $response = json_decode((string)$response->getBody(), true);

        } catch (RequestException $e) {
            //opac  接口异常处理  记录
            $log = [
                'url' => $url,
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ];
            if ($e->hasResponse()) {
                $log['mes'] = $e->getResponse()->getReasonPhrase();
            }
            (new Logger($this->token))
                ->pushHandler(new RotatingFileHandler(storage_path('logs/act.log')))
                ->error('==>', $log);
            $response = $this->errMes;
        }
        return $response;
    }

    /**
     * @param string $path
     * @return string
     */
    public function completionCover(string $path): string
    {
        if (empty($path)) {
            return 'https://wechat-xin.oss-cn-shenzhen.aliyuncs.com/images/nopicture.jpg';
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
//        return Str::replaceLast($this->url, '/') . $path;
        return $this->url . $path;
    }
}
