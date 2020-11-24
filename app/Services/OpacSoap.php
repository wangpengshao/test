<?php

namespace App\Services;

use App\Models\Wxuser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;


/**
 * Class OpacSoap
 * @package App\Services
 */
class OpacSoap
{
    /**
     * @var
     */
    private $url;
    /**
     * @var
     */
    private $client;
    /**
     * @var bool
     */
    private $existError = false;
    /**
     * @var array
     */
    private $errMes = array('errMes' => 'opc接口异常,请稍后再试');

    /**
     * @var
     */
    private $token;

    /**
     * @param string $tokenOrWxuser
     * @param string $restParams
     * @return OpacSoap
     */
    public static function make($tokenOrWxuser, string $restParams)
    {
        return (new static())->initClient($tokenOrWxuser, $restParams);
    }

    /**
     * 不允许从外部调用以防止创建多个实例
     * 要使用单例，必须通过 Singleton::getInstance() 方法获取实例
     */
    private function __construct()
    {
    }

    /**
     * 防止实例被克隆（这会创建实例的副本）
     */
    private function __clone()
    {
    }

    /**
     * @param $tokenOrWxuser
     * @param $restParams
     * @return $this
     */
    private function initClient($tokenOrWxuser, $restParams)
    {
        if ($tokenOrWxuser instanceof Model || is_array($tokenOrWxuser)) {
            $opacUrl = $tokenOrWxuser['opacurl'];
            $this->token = $tokenOrWxuser['token'];
        } else {
            $wxuser = Wxuser::getCache($tokenOrWxuser);
            if (empty($wxuser)) abort(404);
            $this->token = $tokenOrWxuser;
            $opacUrl = $wxuser['opacurl'];
        }
        $opacUrl = $url ?? $opacUrl;

        $this->url = $this->getSoapUrl($opacUrl, $restParams);

        $options = array(
            'cache_wsdl' => 0,
            'connection_timeout' => 10,     //定义连接超时为10秒
            'trace' => 1,
            'exceptions' => 1,
        );

        ini_set('default_socket_timeout', 20);   //定义响应超时为20秒

        try {

            $this->client = new \SoapClient($this->url, $options);

        } catch (\SoapFault $e) {

            $this->existError = true;
            $logger = new Logger('SOAP');
            $logger->pushHandler(new RotatingFileHandler(storage_path('logs/opac.log')));
            $logger->pushProcessor(new WebProcessor(null, ['ip']));
            $logger->error($this->token, [
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ]);
        }
        return $this;
    }

    /**
     * @param $opacUrl
     * @param $restParams
     * @return string
     */
    private function getSoapUrl($opacUrl, $restParams)
    {
        return Str::finish($opacUrl, '/') . $restParams . '?wsdl';
    }

    /**
     * @param       $functionName
     * @param array $arguments
     * @return array|mixed
     */
    public function requestFunction($functionName, array $arguments = array())
    {
        if ($this->existError) return $this->errMes;
        try {
            libxml_disable_entity_loader(false);
            $response = $this->client->__soapCall($functionName, array($functionName => $arguments));

            // 临时处理 获取读者的接口图片字段有返回base64字段，导致转格式失败
            if ($functionName == 'getReader' && isset($response->return->rdphoto)) {
                $response->return->rdphoto = '';
            }

            return json_decode(json_encode($response), true);
        } catch (\SoapFault $e) {
            $logger = new Logger('SOAP');
            $logger->pushHandler(new RotatingFileHandler(storage_path('logs/opac.log')));
            $logger->pushProcessor(new WebProcessor(null, ['ip']));
            $logger->error($this->token, [
                'url' => $this->url,
                'f' => $functionName,
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ]);
            return $this->errMes;
        }
    }

    /**
     * @return mixed
     */
    public function showFunction()
    {
        return $this->client->__getFunctions();
    }

//    public function objectToArray($array)
//    {
//        if (is_object($array)) {
//            $array = (array)$array;
//        }
//        if (is_array($array)) {
//            foreach ($array as $key => $value) {
//                $array[$key] = $this->objectToArray($value);
//            }
//        }
//        return $array;
//    }

}
