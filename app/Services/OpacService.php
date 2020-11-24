<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;


/**
 * Class OpacSoap
 * @package App\Services
 */
class OpacService
{
    /**
     * @var array
     */
    private $errMes = array('errMes' => 'opc接口异常,请稍后再试');


    public static function request(string $url)
    {
        return (new static())->record($url);
    }

    private function record($url)
    {
        $params = [
            'timeout' => 15.0,
            'connect_timeout' => 15.0,
            'http_errors' => true
        ];
        try {

            $http = new Client();
            $response = $http->get($url, $params);
            $response = (string)$response->getBody();

        } catch (RequestException $e) {
            //opac  接口异常处理  记录
            $context = [
                'url' => $url,
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ];
            if ($e->hasResponse()) {
                $context['mes'] = $e->getResponse()->getReasonPhrase();
            }
            $logger = new Logger('HTTP');
            $logger->pushHandler(new RotatingFileHandler(storage_path('logs/opac.log')));
            $logger->pushProcessor(new WebProcessor(null, ['ip']));
            $logger->error('', $context);

            $response = $this->errMes;
        }
        return $response;

    }


}
