<?php

namespace App\Services;

use App\Models\Wechat\OtherConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Class MiResources 电子资源小程序
 * @package App\Services
 */
class MiniResources
{

    /**
     * 根据isbn获取图书馆阅读二维码
     * @param int $wxuser_id
     * @param     $isbn
     * @return array
     */
    public static function searchResources(int $wxuser_id, $isbn)
    {
        $success = [];
        $config = OtherConfig::miniResources($wxuser_id)->first();
        $isbn = self::to13($isbn);
        if ($config === null || $config['mn_resources_sw'] != 1 || empty($config['mn_resources_appid'])
            || empty($config['mn_resources_key']) || empty($isbn))
            return $success;

        $time13 = time() . substr(microtime(), 2, 3);
        $params = [
            'appId' => $config['mn_resources_appid'],
            'timestamp' => $time13,
            'isbn' => $isbn,
            'sign' => md5($config['mn_resources_appid'] . $isbn . $time13 . $config['mn_resources_key'])
        ];
//        $url = 'https://bigdata.rryue.cn/tc/getQrcodeByIsbn?' . http_build_query($params);
        // 接口升级 2020-01-08
        $url = 'https://app.yiwangdujin.com/product/tc/getQrcodeByIsbn?' . http_build_query($params);
        try {
            $http = new Client();
            $response = $http->get($url, [
                'timeout' => 5.0,
                'connect_timeout' => 5.0
            ]);
            $response = json_decode((string)$response->getBody(), true);
        } catch (RequestException $e) {
            $log = [
                'url' => $url,
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ];
            if ($e->hasResponse()) {
                $log['mes'] = $e->getResponse()->getReasonPhrase();
            }
            (new Logger('H'))
                ->pushHandler(new RotatingFileHandler(storage_path('logs/miniResources.log')))
                ->error('==>', $log);
            return $success;
        }

        if ($response['status'] !== 1) return $success;
        return $response['result']['list'];
    }

    /**
     * isbn 10 位 转 13 位
     * @param $isbn
     * @return mixed|string|null
     */
    protected static function to13($isbn)
    {
        $isbn = str_replace('-', '', $isbn);
        //是否是isbn格式
        if (!preg_match('/^\d+x?$/i', $isbn)) {
            return null;
        }
        $strlen = strlen($isbn);
        if ($strlen == 13) {
            return $isbn;
        }
        //是否长度符合10位 无效isbn
        if ($strlen != 10) {
            return null;
        }
        $sum = 0;
        $num = '978' . substr($isbn, 0, 9);
        for ($i = 0; $i < 12; $i++) {
            $n = $num[$i];
            if (($i + 1) % 2 == 0) {
                $sum += $n * 3;
            } else {
                $sum += $n;
            }
        }
        $m = $sum % 10;
        $check = 10 - $m;
        return $num . $check;
    }
}
